<script setup lang="ts">
import { SidebarTrigger } from '@/components/ui/sidebar';
import { onMounted } from 'vue';
import { inject } from 'vue';
import {type RunningData} from '@/types/RunningModel';
import Button from '@/components/ui/button/Button.vue';
import { router } from '@inertiajs/vue3';

const runningList = inject<RunningData>('runningList', []);
onMounted(() => {
    //console.log(runningList.models[0].name);
})


function unloadModel(): void {
    router.post('/unload-model', {
        model: runningList.models[0].name,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-16 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
        </div>
        <Button class="rounded-lg bg-blue-500 px-4 py-2 font-semibold text-white shadow transition hover:bg-blue-600 disabled:opacity-60 cursor-pointer" @click="unloadModel()">UnloadModel</Button>
    </header>
</template>
