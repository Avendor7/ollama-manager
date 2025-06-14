<template>
<div class="w-80">
    <h1>Hello World</h1>
    <div v-for="(model, index) in modelList.models" :key="index" class="p-2 border-b">
        <h2 class="text-lg font-semibold">{{ model.name }}</h2>
        <p class="text-sm text-muted-foreground">{{ model.description }}</p>
        <p class="text-xs text-muted-foreground">Size: {{ (model.size / 1024 / 1024).toFixed(2) }} MB</p>
        <p class="text-xs text-muted-foreground">Modified: {{ model.modifiedAt }}</p>
        <p class="text-xs text-muted-foreground">Digest: {{ model.digest }}</p>
        <div v-if="model.details">
            <p class="text-xs text-muted-foreground">Format: {{ model.details.format }}</p>
            <p class="text-xs text-muted-foreground">Family: {{ model.details.family }}</p>
            <p class="text-xs text-muted-foreground">Parameter Size: {{ model.details.parameterSize }}</p>
            <p class="text-xs text-muted-foreground">Quantization: {{ model.details.quantizationLevel }}</p>
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
})

</script>
<style scoped>

</style>
