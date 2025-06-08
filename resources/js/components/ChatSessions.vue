<template>
    <div class="w-64 border-r border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900 p-4 flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-medium">Chat Sessions</h3>
            <Link
                :href="route('dashboard.new-chat')"
                as="button"
                class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-full transition"
                method="post"
            >
                New Chat
            </Link>
        </div>
        <div class="flex-1 overflow-y-auto space-y-2">
            <Link
                v-for="chat in chatSessions"
                :key="chat.id"
                :href="route('dashboard', { chat_id: chat.id })"
                class="block text-sm p-2 rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-800 transition"
                :class="{ 'bg-zinc-200 dark:bg-zinc-800': chat.id === currentChatId }"
            >
                {{ chat.title || 'New Chat' }}
            </Link>
        </div>
    </div>
</template>
<script setup lang="ts">

import { Link } from '@inertiajs/vue3';
import {inject} from 'vue';
interface ChatSession {
    id: number;
    title: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}
const chatSessions = inject<ChatSession[]>('chatSessions', []);
const currentChatId = inject<number>('currentChatId', 0);
</script>
<style scoped>

</style>
