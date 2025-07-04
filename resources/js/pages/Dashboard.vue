<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs" :chat-sessions="chatSessions" :current-chat-id="currentChatId">
        <div class="flex h-full flex-1 overflow-hidden rounded-b-2xl bg-white dark:bg-zinc-900">
            <!-- Main Chat Area -->
            <div class="flex h-full flex-1 flex-col">
                <!-- Chat Messages -->
                <div ref="chatScroll" class="h-[calc(100vh-160px)] space-y-6 overflow-y-auto bg-zinc-50 px-6 py-4 dark:bg-zinc-900" id="chat-scroll">
                    <template v-for="(msg, idx) in chatMessages" :key="idx">
                        <div v-if="msg.role === 'user'" class="flex justify-end">
                            <div class="max-w-[70%] rounded-xl bg-blue-500 px-4 py-2 whitespace-pre-line text-white shadow-md">
                                {{ msg.content }}
                            </div>
                        </div>
                        <div v-else class="flex justify-start">
                            <div class="flex items-end gap-2">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-green-400 to-blue-600 text-lg font-bold text-white"
                                >
                                    O
                                </div>
                                <div
                                    class="max-w-[70%] rounded-xl bg-zinc-200 px-4 py-2 whitespace-pre-line text-zinc-900 shadow-md dark:bg-zinc-800 dark:text-zinc-100"
                                >
                                    {{ msg.content }}
                                </div>
                            </div>
                        </div>
                    </template>
                    <div v-if="loading" class="flex animate-pulse justify-start">
                        <div class="flex items-end gap-2">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-green-400 to-blue-600 text-lg font-bold text-white"
                            >
                                O
                            </div>
                            <div class="max-w-[70%] rounded-xl bg-zinc-200 px-4 py-2 text-zinc-900 shadow-md dark:bg-zinc-800 dark:text-zinc-100">
                                <span class="opacity-60">Ollama is thinking...</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="showData" class="flex justify-start">
                        <div class="flex items-end gap-2">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-green-400 to-blue-600 text-lg font-bold text-white"
                            >
                                O
                            </div>
                            <div
                                class="max-w-[70%] rounded-xl bg-zinc-200 px-4 py-2 whitespace-pre-line text-zinc-900 shadow-md dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                {{ data }}
                            </div>
                        </div>
                    </div>
                    <div v-if="error" class="px-2 text-sm text-red-500">{{ error }}</div>
                    <div v-if="isFetching">Connecting...</div>
                    <div v-if="isStreaming">Generating...</div>
                </div>

                <!-- Chat Input -->
                <div
                    class="flex h-[80px] w-full items-center gap-2 border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <ModelPopover :modelList="modelList" />
                    <input
                        v-model="input"
                        :disabled="loading"
                        @keydown="handleKey"
                        type="text"
                        placeholder="Type your message..."
                        class="flex-1 rounded-lg bg-zinc-100 px-4 py-2 text-zinc-900 transition focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100"
                    />
                    <button
                        @click="sendMessage"
                        :disabled="loading || !input.trim()"
                        class="rounded-lg bg-blue-500 px-4 py-2 font-semibold text-white shadow transition hover:bg-blue-600 disabled:opacity-60"
                    >
                        <span v-if="!loading">Send</span>
                        <span v-else class="flex items-center gap-1"
                            ><svg class="mr-1 h-4 w-4 animate-spin" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg
                            >Sending</span
                        >
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, nextTick, onMounted, watch, provide } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { useStream } from '@laravel/stream-vue';
import ModelPopover from '@/components/ModelPopover.vue';
import { type RunningData } from '@/types/RunningModel';

interface MessageType {
    id?: number;
    chat_session_id?: number;
    content: string;
    role: string;
    token_count?: number;
    metadata?: any;
    created_at?: string;
    updated_at?: string;
}

interface ChatSession {
    id: number;
    title: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}
interface Model {
    name: string;
    description?: string;
    size: number;
    modifiedAt: string;
    digest: string;
    details?: {
        format: string;
        family: string;
        parameterSize: string;
        quantizationLevel: string;
        families: string[];
        parentModel: string;
    };
}

interface ModelList {
    models: Model[];
}

interface Props {
    user: any;
    messages: MessageType[];
    chatSessions: ChatSession[];
    currentChatId: number;
    modelList?: ModelList;
    runningList?: RunningData;
}

const props = defineProps<Props>();
const selectedModel = ref<string>(props.user.selected_model);

provide('chatSessions', props.chatSessions);
provide('currentChatId', props.currentChatId);
provide('runningList', props.runningList);
provide('selectedModel', selectedModel.value);
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const chatMessages = ref<MessageType[]>([]);
const input = ref('');
const loading = ref(false);
const error = ref('');
const chatScroll = ref<HTMLElement | null>(null);
const showData = ref(false);
const { data, isFetching, isStreaming, send } = useStream("/api/ollama/chat",{
    onData: (data: any) => {
        console.log(data);
        showData.value = true;
    },
    onFinish: () => {
        const assistantMsg: MessageType = { role: 'assistant', content: data.value };
        console.log(data.value);
        showData.value = false;
        chatMessages.value.push(assistantMsg);

    }
});

// Watch for changes in props.messages and update the local chatMessages ref
watch(
    () => props.messages,
    (newMessages) => {
        chatMessages.value = newMessages.length > 0 ? newMessages : [{ role: 'assistant', content: 'Hello! How can I help you today?' }];
        scrollToBottom();
    },
    { immediate: true },
);

function scrollToBottom() {
    nextTick(() => {
        if (chatScroll.value) {
            chatScroll.value.scrollTop = chatScroll.value.scrollHeight;
        }
    });
}

onMounted(scrollToBottom);
onMounted(() => {
    console.log(selectedModel.value);
    //TODO move this to the backend
    if (!props.runningList?.models?.[0]) {
        console.log('no model, starting llama3.1');
        router.post(
            '/load-model',
            {
                model: 'llama3.1',
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }
});
async function sendMessage() {
    if (!input.value.trim() || loading.value) return;
    error.value = '';
    const userMsg: MessageType = { role: 'user', content: input.value };
    chatMessages.value.push(userMsg);
    loading.value = true;
    scrollToBottom();
    try {
        send({
            prompt: input.value,
            chat_session_id: props.currentChatId,
        });
        input.value = '';
        scrollToBottom();
    } catch (e: any) {
        error.value = e.message || 'Something went wrong.';
    } finally {

        loading.value = false;
        scrollToBottom();
    }
}

function handleKey(e: KeyboardEvent) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}
</script>
