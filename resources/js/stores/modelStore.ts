import { defineStore } from 'pinia';
import { type RunningData } from '@/types/RunningModel';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

interface Model {
    name: string;
    description?: string;
    size: number;
    modifiedAt: string;
    digest: string;
    details?: {
        format: string;
        family: string;
        parameterSize: string;
        quantizationLevel: string;
        families: string[];
        parentModel: string;
    };
}

interface ModelList {
    models: Model[];
}

export const useModelStore = defineStore('model', {
    state: () => ({
        modelList: null as ModelList | null,
        runningList: null as RunningData | null,
        selectedModel: '' as string,
        pollingInterval: null as number | null, // To store the interval ID
        isPolling: false // Flag to track if polling is active
    }),

    getters: {
        getModelList: (state) => state.modelList,
        getRunningList: (state) => state.runningList,
        getSelectedModel: (state) => state.selectedModel,
        getIsPolling: (state) => state.isPolling,

        // Get models grouped by family
        modelsByFamily: (state) => {
            if (!state.modelList?.models?.length) return {};

            const grouped: Record<string, Model[]> = {};

            state.modelList.models.forEach((model: Model) => {
                const family = model.details?.family || 'Unknown';
                if (!grouped[family]) {
                    grouped[family] = [];
                }
                grouped[family].push(model);
            });

            return grouped;
        }
    },

    actions: {
        setModelList(modelList: ModelList) {
            this.modelList = modelList;
        },

        setRunningList(runningList: RunningData) {
            this.runningList = runningList;
        },

        setSelectedModel(modelName: string) {
            this.selectedModel = modelName;
        },

        // Fetch the running model data from the API
        async fetchRunningModel() {
            try {
                const response = await axios.get<RunningData>('/ollama/running-model');
                if (response.data) {
                    this.setRunningList(response.data);
                }
            } catch (error) {
                console.error('Error fetching running model:', error);
            }
        },

        // Start polling for running model updates
        startPolling() {
            if (this.isPolling) return; // Don't start if already polling

            this.isPolling = true;

            // Fetch immediately on start
            this.fetchRunningModel();

            // Set up interval to fetch every minute (60000ms)
            this.pollingInterval = window.setInterval(() => {
                this.fetchRunningModel();
            }, 60000);
        },

        // Stop polling for running model updates
        stopPolling() {
            if (this.pollingInterval !== null) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
            this.isPolling = false;
        },

        loadModel(modelName: string) {
            this.setSelectedModel(modelName);

            router.post(
                '/load-model',
                {
                    model: modelName,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                },
            );
        }
    }
});
