<script setup lang="ts">
import AppContent from '@/components/layout/AppContent.vue';
import AppShell from '@/components/layout/AppShell.vue';
import AppSidebar from '@/components/layout/AppSidebar.vue';
import AppHeader from '@/components/layout/AppSidebarHeader.vue';
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
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
