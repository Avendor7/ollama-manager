<script setup lang="ts">
import { SidebarTrigger } from '@/components/ui/sidebar';
import { onMounted } from 'vue';
import Button from '@/components/ui/button/Button.vue';
import { useModelStore } from '@/stores/modelStore';
import ModelPopover from '@/components/ModelPopover.vue';
import { router } from '@inertiajs/vue3';
// Access the store
const modelStore = useModelStore();

// Now you can use the store's state or actions, e.g.:

onMounted(() => {
    //console.log(runningList.models[0].name);
    console.log(modelStore.getRunningList?.models[0]);

})


function unloadModel(): void {
    router.post('/unload-model', {
        model: modelStore.getRunningList?.models[0].name,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

function bytesToGigabytes(bytes: number): string {
    const gigabytes = bytes / (1024 * 1024 * 1024);
    return gigabytes.toFixed(2);
}

</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-16 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
        </div>
        <ModelPopover />

        <Button v-if="modelStore.getRunningList?.models[0]" class="rounded-lg bg-blue-500 px-4 py-2 font-semibold text-white shadow transition hover:bg-blue-600 disabled:opacity-60 cursor-pointer" @click="unloadModel()">UnloadModel</Button>
        <div>
            <span>Running Model: <b>{{modelStore.getRunningList?.models[0]?.name}}</b></span>
            <span> Size: <b>{{bytesToGigabytes(modelStore.getRunningList?.models[0]?.size ?? 0)}}GB</b></span>
        </div>
    </header>
</template>
