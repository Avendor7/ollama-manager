<template>
  <div ref="chatScroll" class="h-[calc(100vh-160px)] space-y-6 overflow-y-auto bg-zinc-50 px-6 py-4 dark:bg-zinc-900" id="chat-scroll">
    <template v-for="(msg, idx) in messages" :key="idx">
      <UserMessage v-if="msg.role === 'user'" :content="msg.content" />
      <AssistantMessage v-else :content="msg.content" />
    </template>
    <LoadingIndicator v-if="loading" />
    <AssistantMessage v-if="showData" :content="data" />
    <div v-if="error" class="px-2 text-sm text-red-500">{{ error }}</div>
    <div v-if="isFetching">Connecting...</div>
    <div v-if="isStreaming">Generating...</div>
  </div>
</template>

<script setup lang="ts">
import { ref, nextTick, onMounted, watch } from 'vue';
import UserMessage from './UserMessage.vue';
import AssistantMessage from './AssistantMessage.vue';
import LoadingIndicator from './LoadingIndicator.vue';
import { type MessageType } from '@/types/chat';

const props = defineProps<{
  messages: MessageType[];
  loading: boolean;
  error: string;
  data: string;
  showData: boolean;
  isFetching: boolean;
  isStreaming: boolean;
}>();

const chatScroll = ref<HTMLElement | null>(null);

function scrollToBottom() {
  nextTick(() => {
    if (chatScroll.value) {
      chatScroll.value.scrollTop = chatScroll.value.scrollHeight;
    }
  });
}

watch(() => props.messages, scrollToBottom, { immediate: true });
watch(() => props.loading, scrollToBottom);
watch(() => props.showData, scrollToBottom);

onMounted(scrollToBottom);
</script>
