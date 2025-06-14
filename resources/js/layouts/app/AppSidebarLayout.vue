<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import ModelSidebar from '@/components/ModelSidebar.vue';
import type { BreadcrumbItemType } from '@/types';
import { provide } from 'vue';
interface ChatSession {
    id: number;
    title: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}
interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    chatSessions?: ChatSession[];
    currentChatId?: number;
    modelList?: any[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    chatSessions: () => [],
    currentChatId: () => 0,
    modelList: () => [],
});

// Provide the chat sessions to all child components
provide('chatSessions', props.chatSessions);
provide('currentChatId', props.currentChatId);
provide('modelList', props.modelList);
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
        <ModelSidebar :model-list="modelList"/>
    </AppShell>
</template>
