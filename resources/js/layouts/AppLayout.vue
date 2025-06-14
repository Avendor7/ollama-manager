<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    chatSessions?: any[];
    currentChatId?: number;
    modelList?: any[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    chatSessions: () => [],
    currentChatId: () => 0,
    modelList: () => [],
});
</script>

<template>
    <AppLayout
        :breadcrumbs="breadcrumbs"
        :chat-sessions="chatSessions"
        :current-chat-id="currentChatId"
        :model-list="modelList"
    >
        <slot />
    </AppLayout>
</template>
