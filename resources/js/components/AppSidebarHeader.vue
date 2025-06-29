<script setup lang="ts">
import { SidebarTrigger } from '@/components/ui/sidebar';
import { onMounted } from 'vue';
import { inject } from 'vue';
import {type RunningData} from '@types/RunningModel';

const runningList = inject<RunningData>('runningList', []);
onMounted(() => {
    console.log(runningList.models[0].name);
})
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-16 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
        </div>
        <span>
            {{runningList.models?.[0]?.name}}
        </span>
    </header>
</template>
