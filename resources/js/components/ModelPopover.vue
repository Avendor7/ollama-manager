<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button class="relative inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-all duration-200 hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                <div class="flex items-center gap-2">
                    <span class="truncate max-w-[180px]">
                        {{ modelStore.getRunningList?.models[0] ? modelStore.getRunningList.models[0].name : 'Load Model' }}
                    </span>
                    <ChevronDown class="h-4 w-4 text-gray-500 transition-transform duration-200 group-data-[state=open]:rotate-180 dark:text-gray-400" />
                </div>

                <!-- Eject Button (always visible, enabled only when a model is running) -->
                <div
                    @click.stop="modelStore.getRunningList?.models[0] && unloadModel()"
                    class="ml-1.5 flex h-6 w-6 items-center justify-center rounded-full transition-colors duration-200"
                    :class="modelStore.getRunningList?.models[0]
                        ? 'bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-800/50'
                        : 'bg-gray-100 text-gray-400 dark:bg-gray-700/50 dark:text-gray-500'"
                    :title="modelStore.getRunningList?.models[0] ? 'Eject Model' : 'No Model Running'"
                >
                    <Upload class="h-3.5 w-3.5" />
                </div>

                <!-- Active indicator -->
                <span v-if="modelStore.getRunningList?.models[0]" class="absolute -right-1 -top-1 flex h-3 w-3">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex h-3 w-3 rounded-full bg-green-500"></span>
                </span>
            </Button>
        </PopoverTrigger>

        <PopoverContent class="max-h-[80vh] w-[100%] overflow-hidden" side="top" align="center">
            <div v-if="modelStore.modelList && modelStore.modelList.models && modelStore.modelList.models.length > 0">
                <!-- Sort Controls -->
                <div class="mb-3 flex gap-2">
                    <select v-model="sortBy" class="bg-background rounded border px-2 py-1 text-xs">
                        <option value="name">Name</option>
                        <option value="size">Size</option>
                        <option value="family">Family</option>
                        <option value="parameterSize">Parameters</option>
                    </select>
                    <button @click="toggleSortOrder" class="hover:bg-accent rounded border px-2 py-1 text-xs">
                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                    </button>
                </div>

                <!-- Model Status Display (when a model is running) -->
                <div v-if="modelStore.getRunningList?.models[0]" class="mb-3 flex items-center justify-between rounded-md bg-blue-50 p-2 dark:bg-blue-950/30">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                        <span class="text-xs">
                            Running: <span class="font-semibold">{{ modelStore.getRunningList.models[0].name }}</span>
                            ({{ formatSize(modelStore.getRunningList.models[0].size) }})
                        </span>
                    </div>
                    <button
                        @click="unloadModel()"
                        class="flex items-center gap-1 rounded bg-red-100 px-2 py-1 text-xs text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                    >
                        <Upload class="h-3 w-3" />
                        <span>Eject</span>
                    </button>
                </div>

                <!-- Sectioned Container -->
                <div class="max-h-[70vh] space-y-4 overflow-y-auto pr-2">
                    <div v-for="(models, family) in modelsByFamily" :key="family" class="space-y-2">
                        <button
                            @click="toggleFamily(family)"
                            class="flex w-full items-center justify-between rounded bg-zinc-100 px-2 py-1.5 text-left text-sm font-bold dark:bg-zinc-800"
                        >
                            <span>{{ family }} ({{ models.length }})</span>
                            <span>{{ expandedFamilies[family] ? '▼' : '►' }}</span>
                        </button>

                        <!-- Models Grid for this Family -->
                        <div v-if="expandedFamilies[family]" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div
                                v-for="(model, index) in models"
                                :key="index"
                                @click="loadModel(model.name)"
                                class="group relative hover:bg-accent/50 cursor-pointer rounded-lg border p-3 transition-colors"
                                :class="{'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20': modelStore.getRunningList?.models[0]?.name === model.name}"
                            >
                                <h2 class="mb-1 truncate text-sm font-semibold">{{ model.name }}</h2>
                                <p v-if="model.description" class="text-muted-foreground mb-2 line-clamp-2 text-xs">
                                    {{ model.description }}
                                </p>
                                <div class="space-y-1">
                                    <p class="text-muted-foreground text-xs"><span class="font-medium">Size:</span> {{ formatSize(model.size) }}</p>
                                    <div v-if="model.details" class="space-y-1">
                                        <p class="text-muted-foreground text-xs">
                                            <span class="font-medium">Params:</span> {{ model.details.parameterSize }}
                                        </p>
                                        <p class="text-muted-foreground text-xs">
                                            <span class="font-medium">Quant:</span> {{ model.details.quantizationLevel }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Running indicator for currently loaded model -->
                                <div
                                    v-if="modelStore.getRunningList?.models[0]?.name === model.name"
                                    class="absolute right-2 top-2 flex h-5 w-5 items-center justify-center rounded-full bg-green-500 text-white"
                                    title="Currently Running"
                                >
                                    <Check class="h-3 w-3" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-muted-foreground p-4 text-center">No models available</div>
        </PopoverContent>
    </Popover>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { useModelStore } from '@/stores/modelStore';
import { router } from '@inertiajs/vue3';
import { ChevronDown, Check, Upload } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

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

const modelStore = useModelStore();

type SortKey = 'name' | 'size' | 'family' | 'parameterSize';
type SortOrder = 'asc' | 'desc';

const sortBy = ref<SortKey>('name');
const sortOrder = ref<SortOrder>('asc');

const sortedModels = computed((): Model[] => {
    if (!modelStore.modelList?.models?.length) return [];

    return [...modelStore.modelList.models].sort((a: Model, b: Model): number => {
        let aVal: string | number;
        let bVal: string | number;

        switch (sortBy.value) {
            case 'size':
                aVal = a.size;
                bVal = b.size;
                break;
            case 'family':
                aVal = a.details?.family || '';
                bVal = b.details?.family || '';
                break;
            case 'parameterSize':
                aVal = a.details?.parameterSize || '';
                bVal = b.details?.parameterSize || '';
                break;
            default: // name
                aVal = a.name;
                bVal = b.name;
        }

        let result: number;
        if (typeof aVal === 'string') {
            result = aVal.localeCompare(bVal as string);
        } else {
            result = aVal - (bVal as number);
        }

        return sortOrder.value === 'desc' ? -result : result;
    });
});

// Define a type for the grouped models object
type ModelsByFamilyType = Record<string, Model[]>;

const modelsByFamily = computed((): ModelsByFamilyType => {
    if (!modelStore.modelList?.models?.length) return {} as ModelsByFamilyType;

    // Group models by family with proper typing
    const grouped: ModelsByFamilyType = {};

    sortedModels.value.forEach((model: Model) => {
        const family = model.details?.family || 'Unknown';
        if (!grouped[family]) {
            grouped[family] = [];
        }
        grouped[family].push(model);
    });

    return grouped;
});

// Define a type for the expanded families object
type ExpandedFamiliesType = Record<string, boolean>;

const expandedFamilies = ref<ExpandedFamiliesType>({});

function toggleFamily(family: string): void {
    expandedFamilies.value[family] = !expandedFamilies.value[family];
}

function loadModel(modelName: string): void {
    modelStore.loadModel(modelName);
}

function unloadModel(): void {
    if (modelStore.getRunningList?.models[0]) {
        router.post('/unload-model', {
            model: modelStore.getRunningList.models[0].name,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }
}

// Initialize all families as expanded
watch(
    modelsByFamily,
    (newValue) => {
        Object.keys(newValue).forEach((family) => {
            if (expandedFamilies.value[family] === undefined) {
                expandedFamilies.value[family] = true;
            }
        });
    },
    { immediate: true },
);

const toggleSortOrder = (): void => {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
};

const formatSize = (bytes: number): string => {
    return (bytes / 1024 / 1024 / 1024).toFixed(2) + ' GB';
};
</script>
