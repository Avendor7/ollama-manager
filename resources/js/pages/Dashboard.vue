<template>
  <Head title="Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col bg-white dark:bg-zinc-900 overflow-hidden">
      <!-- Chat Messages -->
      <div ref="chatScroll" class="flex-1 overflow-y-auto px-6 py-4 space-y-6 bg-zinc-50 dark:bg-zinc-900" id="chat-scroll">
        <template v-for="(msg, idx) in messages" :key="idx">
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
        <div v-if="error" class="text-red-500 px-2 text-sm">{{ error }}</div>
          <div v-if="isFetching">Connecting...</div>
          <div v-if="isStreaming">Generating...</div>
      </div>
      <!-- Chat Input -->
      <div class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-3 flex items-center gap-2">
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
  </AppLayout>
</template>
<script setup lang="ts">
import { ref, nextTick, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useStream } from "@laravel/stream-vue"

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const messages = ref([
    { role: 'assistant', content: 'Of course! What do you need help with today?' },
]);
const input = ref('');
const loading = ref(false);
const error = ref('');
const chatScroll = ref<HTMLElement | null>(null);
const { data, isFetching, isStreaming, send } = useStream("/api/ollama/chat");

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
    const userMsg = { role: 'user', content: input.value };
    messages.value.push(userMsg);
    loading.value = true;
    scrollToBottom();
    try {
        send({
            prompt: input.value,
        });

        input.value = '';
        scrollToBottom();
    } catch (e: any) {
        error.value = e.message || 'Something went wrong.';
    } finally {
        messages.value.push({ role: 'assistant', content: data.value });
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
