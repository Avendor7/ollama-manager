<template>
    <div class="w-80 max-h-screen overflow-y-auto">
        <h1>Hello World</h1>
        <div v-for="(model, index) in modelList.models" :key="index" class="border-b p-2 ">
            <h2 class="text-lg font-semibold">{{ model.name }}</h2>
            <p class="text-muted-foreground text-sm">{{ model.description }}</p>
            <p class="text-muted-foreground text-xs">Size: {{ (model.size / 1024 / 1024 / 1024).toFixed(2) }} GB</p>
            <div v-if="model.details">
                <p class="text-muted-foreground text-xs">Family: {{ model.details.family }}</p>
                <p class="text-muted-foreground text-xs">Parameter Size: {{ model.details.parameterSize }}</p>
                <p class="text-muted-foreground text-xs">Quantization: {{ model.details.quantizationLevel }}</p>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import { onMounted } from 'vue';

const props = defineProps<{
    modelList: {
        models: Array<{
            name: string;
            description?: string;
            size: number;
            modifiedAt: string;
            digest: string;
            details: {
                format: string;
                family: string;
                parameterSize: string;
                quantizationLevel: string;
                families: string[];
                parentModel: string;
            };
        }>;
    };
}>();

onMounted(() => {
    console.log('Model List:', props.modelList);
});
</script>
<style scoped></style>
