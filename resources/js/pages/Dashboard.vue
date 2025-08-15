<template>
  <Head title="Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs" :chat-sessions="chatSessions" :current-chat-id="currentChatId">
    <div class="flex h-full flex-1 overflow-hidden rounded-b-2xl bg-white dark:bg-zinc-900">
      <div class="mx-auto flex h-full w-full max-w-[50%] flex-1 flex-col">
        <ChatMessages
          :messages="chatMessages"
          :loading="loading"
          :error="error"
          :data="data"
          :show-data="showData"
          :is-fetching="isFetching"
          :is-streaming="isStreaming"
        />
        <ChatInput :disabled="loading" @send="sendMessage" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, provide } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ChatMessages from '@/components/chat/ChatMessages.vue';
import ChatInput from '@/components/chat/ChatInput.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { useStream } from '@laravel/stream-vue';
import { type RunningData } from '@/types/RunningModel';
import { useModelStore } from '@/stores/modelStore';
import { type MessageType, type ChatSession, type ModelList } from '@/types/chat';

interface Props {
    user: any;
    messages: MessageType[];
    chatSessions: ChatSession[];
    currentChatId: number;
    modelList?: ModelList;
    runningList?: RunningData;
}

const props = defineProps<Props>();
const modelStore = useModelStore();

// Initialize the store with data from props
if (props.modelList) {
    modelStore.setModelList(props.modelList);
}
if (props.runningList) {
    modelStore.setRunningList(props.runningList);
}
if (props.user.selected_model) {
    modelStore.setSelectedModel(props.user.selected_model);
}

provide('chatSessions', props.chatSessions);
provide('currentChatId', props.currentChatId);
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const chatMessages = ref<MessageType[]>([]);
const loading = ref(false);
const error = ref('');
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
    },
    { immediate: true },
);
onMounted(() => {
    console.log(modelStore.selectedModel);

    // Start polling for running model updates every minute
    modelStore.startPolling();

    //TODO move this to the backend
    if (!modelStore.runningList?.models?.[0]) {
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

// Clean up polling when component is unmounted
onUnmounted(() => {
    modelStore.stopPolling();
});

async function sendMessage(message: string) {
    if (!message.trim() || loading.value) return;
    error.value = '';
    const userMsg: MessageType = { role: 'user', content: message };
    chatMessages.value.push(userMsg);
    loading.value = true;

    try {
        send({
            prompt: message,
            chat_session_id: props.currentChatId,
        });
    } catch (e: any) {
        error.value = e.message || 'Something went wrong.';
    } finally {
        loading.value = false;
    }
}
</script>
