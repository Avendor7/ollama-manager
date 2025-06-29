<template>
    <Popover>
        <PopoverTrigger class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg px-4 py-2 font-semibold shadow transition disabled:opacity-60">
            {{runningList.models?.[0]?.name}}
        </PopoverTrigger>
        <PopoverContent class="w-[80%] max-h-[80vh] overflow-hidden" side="top" align="start">
            <div v-if="modelList && modelList.models && modelList.models.length > 0">
                <!-- Sort Controls -->
                <div class="mb-3 flex gap-2">
                    <select
                        v-model="sortBy"
                        class="text-xs border rounded px-2 py-1 bg-background"
                    >
                        <option value="name">Name</option>
                        <option value="size">Size</option>
                        <option value="family">Family</option>
                        <option value="parameterSize">Parameters</option>
                    </select>
                    <button
                        @click="toggleSortOrder"
                        class="text-xs border rounded px-2 py-1 hover:bg-accent"
                    >
                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                    </button>
                </div>

                <!-- Sectioned Container -->
                <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
                    <div v-for="(models, family) in modelsByFamily" :key="family" class="space-y-2">
                        <button @click="toggleFamily(family)" class="w-full text-left text-sm font-bold px-1 py-1 bg-zinc-100 dark:bg-zinc-800 rounded flex justify-between items-center">
                            <span>{{ family }} ({{ models.length }})</span>
                            <span>{{ expandedFamilies[family] ? '▼' : '►' }}</span>
                        </button>

                        <!-- Models Grid for this Family -->
                        <div v-if="expandedFamilies[family]" class="grid grid-cols-4 sm:grid-cols-4 gap-3">
                            <div
                                v-for="(model, index) in models"
                                :key="index"
                                class="border rounded-lg p-3 hover:bg-accent/50 transition-colors cursor-pointer"
                            >
                                <h2 class="text-sm font-semibold mb-1 truncate">{{ model.name }}</h2>
                                <p v-if="model.description" class="text-muted-foreground text-xs mb-2 line-clamp-2">
                                    {{ model.description }}
                                </p>
                                <div class="space-y-1">
                                    <p class="text-muted-foreground text-xs">
                                        <span class="font-medium">Size:</span> {{ formatSize(model.size) }}
                                    </p>
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
            <div v-else class="p-4 text-center text-muted-foreground">
                No models available
            </div>
        </PopoverContent>
    </Popover>
</template>

<script setup lang="ts">
import { ref, computed, watch, Ref } from 'vue';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { inject } from 'vue';
import {type RunningData} from '@types/RunningModel';

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

interface Props {
    modelList?: ModelList;
}
const runningList = inject<RunningData>('runningList', []);

const props = defineProps<Props>();

type SortKey = 'name' | 'size' | 'family' | 'parameterSize'
type SortOrder = 'asc' | 'desc'

const sortBy: Ref<SortKey> = ref('name')
const sortOrder: Ref<SortOrder> = ref('asc')

const sortedModels = computed((): Model[] => {
    if (!props.modelList?.models?.length) return []

    return [...props.modelList.models].sort((a: Model, b: Model): number => {
        let aVal: string | number
        let bVal: string | number

        switch (sortBy.value) {
            case 'size':
                aVal = a.size
                bVal = b.size
                break
            case 'family':
                aVal = a.details?.family || ''
                bVal = b.details?.family || ''
                break
            case 'parameterSize':
                aVal = a.details?.parameterSize || ''
                bVal = b.details?.parameterSize || ''
                break
            default: // name
                aVal = a.name
                bVal = b.name
        }

        let result: number
        if (typeof aVal === 'string') {
            result = aVal.localeCompare(bVal as string)
        } else {
            result = aVal - (bVal as number)
        }

        return sortOrder.value === 'desc' ? -result : result
    })
});

// Define a type for the grouped models object
type ModelsByFamilyType = Record<string, Model[]>;

const modelsByFamily = computed((): ModelsByFamilyType => {
    if (!props.modelList?.models?.length) return {} as ModelsByFamilyType;

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

const expandedFamilies = ref<ExpandedFamiliesType>({})

function toggleFamily(family: string): void {
    expandedFamilies.value[family] = !expandedFamilies.value[family]
}

// Initialize all families as expanded
watch(modelsByFamily, (newValue) => {
    Object.keys(newValue).forEach(family => {
        if (expandedFamilies.value[family] === undefined) {
            expandedFamilies.value[family] = true
        }
    })
}, { immediate: true })

const toggleSortOrder = (): void => {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
}

const formatSize = (bytes: number): string => {
    return (bytes / 1024 / 1024 / 1024).toFixed(2) + ' GB'
}
</script>
