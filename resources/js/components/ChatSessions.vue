<template>
    <div class="h-full px-2 py-0">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-bold">Chats</h3>
            <button @click="createNewChat" class="flex h-7 w-7 items-center justify-center rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-800">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto">
            <Link
                v-for="chat in chatSessions"
                :key="chat.id"
                :href="route('dashboard', { chat_id: chat.id })"
                class="flex items-center rounded-lg p-2 text-sm transition hover:bg-zinc-200 dark:hover:bg-zinc-800"
                :class="{ 'bg-zinc-200 dark:bg-zinc-800': chat.id === currentChatId }"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="mr-2 h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10z" />
                </svg>
                {{ chat.title || 'New Chat' }}
                <button
                    class="ml-auto flex h-6 w-6 items-center justify-center rounded-lg hover:bg-zinc-300 dark:hover:bg-zinc-700"
                    @click.stop.prevent="confirmAndDelete(chat.id)"
                >
                    <Trash2 class="h-4 w-4" />
                </button>

            </Link>
        </div>
    </div>
</template>
<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { inject } from 'vue';
import { Trash2 } from 'lucide-vue-next';

interface ChatSession {
    id: number;
    title: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}
const chatSessions = inject<ChatSession[]>('chatSessions', []);
const currentChatId = inject<number>('currentChatId', 0);

const createNewChat = () => {
    router.post(
        route('dashboard.new-chat'),
        {},
        {
            preserveState: false,
            preserveScroll: true,
        },
    );
};
function confirmAndDelete(chatId: number) {
    if (confirm('Are you sure you want to delete this chat?')) {
        router.delete(route('dashboard.destroy', { chat: chatId }), {
            preserveState: false,
            onSuccess: () => {
                // Optionally reload page or update chat list here
            },
            onError: (errors) => {
                alert('Failed to delete chat.');
                console.error(errors);
            },
        });
    }
}

</script>
<style scoped></style>
