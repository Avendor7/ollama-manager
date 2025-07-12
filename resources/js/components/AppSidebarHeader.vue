<script setup lang="ts">
import { SidebarTrigger } from '@/components/ui/sidebar';
import { onMounted } from 'vue';
import { useModelStore } from '@/stores/modelStore';
import ModelPopover from '@/components/ModelPopover.vue';

// Access the store
const modelStore = useModelStore();

onMounted(() => {
    console.log(modelStore.getRunningList?.models[0]);
})

function bytesToGigabytes(bytes: number): string {
    const gigabytes = bytes / (1024 * 1024 * 1024);
    return gigabytes.toFixed(2);
}
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-4 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-16 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
        </div>

        <div class="flex justify-center gap-2 w-full">
            <ModelPopover />
        </div>

        <div v-if="modelStore.getRunningList?.models[0]" class="hidden md:flex items-center gap-2 text-sm">
            <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
            <span class="text-muted-foreground">
                {{ bytesToGigabytes(modelStore.getRunningList.models[0].size) }}GB
            </span>
        </div>
    </header>
</template>
