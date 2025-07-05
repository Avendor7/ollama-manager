import { defineStore } from 'pinia';
import { type RunningData, type RunningModel } from '@/types/RunningModel';
import { router } from '@inertiajs/vue3';

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
        selectedModel: '' as string
    }),

    getters: {
        getModelList: (state) => state.modelList,
        getRunningList: (state) => state.runningList,
        getSelectedModel: (state) => state.selectedModel,

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
