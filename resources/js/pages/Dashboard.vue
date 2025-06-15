<template>
  <Head title="Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs" :chat-sessions="chatSessions"
             :current-chat-id="currentChatId" :model-list="modelList"
  >
    <div class="flex h-full flex-1 bg-white dark:bg-zinc-900 overflow-hidden rounded-b-2xl">
      <!-- Main Chat Area -->
      <div class="flex-1 flex flex-col h-full">
        <!-- Chat Messages -->
        <div ref="chatScroll" class="overflow-y-auto px-6 py-4 space-y-6 bg-zinc-50 dark:bg-zinc-900 h-[calc(100vh-145px)]" id="chat-scroll">
          <template v-for="(msg, idx) in chatMessages" :key="idx">
            <div v-if="msg.role === 'user'" class="flex justify-end">
              <div class="max-w-[70%] bg-blue-500 text-white rounded-xl px-4 py-2 shadow-md whitespace-pre-line">
                {{ msg.content }}
              </div>
            </div>
            <div v-else class="flex justify-start">
              <div class="flex items-end gap-2">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg">O</div>
                <div class="max-w-[70%] bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-xl px-4 py-2 shadow-md whitespace-pre-line">
                    {{ msg.content }}
                </div>
              </div>
            </div>

          </template>
          <div v-if="loading" class="flex justify-start animate-pulse">
            <div class="flex items-end gap-2">
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg">O</div>
              <div class="max-w-[70%] bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-xl px-4 py-2 shadow-md">
                <span class="opacity-60">Ollama is thinking...</span>
              </div>
            </div>
          </div>
          <div v-if="showData" class="flex justify-start">
            <div class="flex items-end gap-2">
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg">O</div>
              <div class="max-w-[70%] bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-xl px-4 py-2 shadow-md whitespace-pre-line">
                {{ data }}
              </div>
            </div>
          </div>
          <div v-if="error" class="text-red-500 px-2 text-sm">{{ error }}</div>
          <div v-if="isFetching">Connecting...</div>
          <div v-if="isStreaming">Generating...</div>
        </div>

        <!-- Chat Input -->
        <div class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-3 flex items-center gap-2 w-full">
        <Popover >
            <PopoverTrigger class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg px-4 py-2 font-semibold shadow transition disabled:opacity-60">
                Open popover
            </PopoverTrigger>
            <PopoverContent>
                <div v-for="(model, index) in modelList.models" :key="index" class="border-b p-2 ">
                    <h2 class="text-lg font-semibold">{{ model.name }}</h2>
                    <p class="text-muted-foreground text-sm">{{ model.description }}</p>
                    <p class="text-muted-foreground text-xs">Size: {{ (model.size / 1024 / 1024 / 1024).toFixed(2) }} GB</p>
                    <div v-if="model.details">
                        <p class="text-muted-foreground text-xs">Family: {{ model.details.family }}</p>
                        <p class="text-muted-foreground text-xs">Parameter Size: {{ model.details.parameterSize }}</p>
                        <p class="text-muted-foreground text-xs">Quantization: {{ model.details.quantizationLevel }}</p>
                    </div>
                </div>
            </PopoverContent>
        </Popover>
        <input
            v-model="input"
            :disabled="loading"
            @keydown="handleKey"
            type="text"
            placeholder="Type your message..."
            class="flex-1 bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
          />
          <button
            @click="sendMessage"
            :disabled="loading || !input.trim()"
            class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg px-4 py-2 font-semibold shadow transition disabled:opacity-60"
          >
            <span v-if="!loading">Send</span>
            <span v-else class="flex items-center gap-1"><svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>Sending</span>
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
import { Head } from '@inertiajs/vue3';
import { useStream } from "@laravel/stream-vue"
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
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
    modelList: {
        models: Array<{
            name: string;
            description?: string;
            size: number;
            modifiedAt: string;
            digest: string;
            details: {
                format: string;
                family: string;
                parameterSize: string;
                quantizationLevel: string;
                families: string[];
                parentModel: string;
            };
        }>;
    };
}
interface Props {
  user: any;
  messages: MessageType[];
  chatSessions: ChatSession[];
  currentChatId: number;
  modelList?: Model[];
}

const props = defineProps<Props>();
provide('chatSessions', props.chatSessions);
provide('currentChatId', props.currentChatId);

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
watch(() => props.messages, (newMessages) => {
    chatMessages.value = newMessages.length > 0 ?
        newMessages :
        [{ role: 'assistant', content: 'Hello! How can I help you today?' }];
    scrollToBottom();
}, { immediate: true });

function scrollToBottom() {
    nextTick(() => {
        if (chatScroll.value) {
            chatScroll.value.scrollTop = chatScroll.value.scrollHeight;
        }
    });
}

onMounted(scrollToBottom);

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
