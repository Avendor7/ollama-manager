<template>
    <Popover>
        <PopoverTrigger class="rounded-lg bg-blue-500 px-4 py-2 font-semibold text-white shadow transition hover:bg-blue-600 disabled:opacity-60 cursor-pointer">
            {{ modelStore.selectedModel }}
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

                <!-- Sectioned Container -->
                <div class="max-h-[70vh] space-y-4 overflow-y-auto pr-2">
                    <div v-for="(models, family) in modelsByFamily" :key="family" class="space-y-2">
                        <button
                            @click="toggleFamily(family)"
                            class="flex w-full items-center justify-between rounded bg-zinc-100 px-1 py-1 text-left text-sm font-bold dark:bg-zinc-800"
                        >
                            <span>{{ family }} ({{ models.length }})</span>
                            <span>{{ expandedFamilies[family] ? '▼' : '►' }}</span>
                        </button>

                        <!-- Models Grid for this Family -->
                        <div v-if="expandedFamilies[family]" class="grid grid-cols-4 gap-3 sm:grid-cols-4">
                            <div
                                v-for="(model, index) in models"
                                :key="index"
                                @click="loadModel(model.name)"
                                class="hover:bg-accent/50 cursor-pointer rounded-lg border p-3 transition-colors"
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
