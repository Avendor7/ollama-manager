<template>
<div class="w-80">
    <h1>Hello World</h1>
    <div v-for="(model, index) in modelList.models" :key="index" class="p-2 border-b">
        <h2 class="text-lg font-semibold">{{ model.name }}</h2>
        <p class="text-sm text-muted-foreground">{{ model.description }}</p>
        <p class="text-xs text-muted-foreground">Size: {{ (model.size / 1024 / 1024).toFixed(2) }} MB</p>
    </div>
</div>
</template>
<script setup lang="ts">

import { onMounted } from 'vue';

const props = defineProps<{
    modelList: {
        models: Array<{
            name: string;
            description: string;
            size: number;
        }>;
    };
}>();

onMounted(() => {
    console.log('Model List:', props.modelList);
})

</script>
<style scoped>

</style>
