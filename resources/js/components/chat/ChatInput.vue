<template>
  <div class="relative w-full border-t border-zinc-200 bg-white px-2 py-3 dark:border-zinc-800 dark:bg-zinc-900">
    <div class="relative flex items-center">
      <input
        v-model="inputValue"
        :disabled="disabled"
        @keydown="handleKey"
        type="text"
        placeholder="Type your message..."
        class="w-full rounded-full bg-zinc-100 py-3 pl-5 pr-12 text-zinc-900 transition focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-zinc-800 dark:text-zinc-100"
      />
      <button
        @click="sendMessage"
        :disabled="disabled || !inputValue.trim()"
        class="absolute right-3 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-full bg-blue-500 p-0 text-white transition hover:bg-blue-600 disabled:opacity-50"
        :class="{ 'opacity-50': disabled || !inputValue.trim() }"
      >
        <Send v-if="!disabled" class="h-5 w-5" />
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Send } from 'lucide-vue-next';

const props = defineProps<{
  disabled: boolean;
}>();

const emit = defineEmits<{
  (e: 'send', message: string): void;
}>();

const inputValue = ref('');

function sendMessage() {
  if (!inputValue.value.trim() || props.disabled) return;
  emit('send', inputValue.value);
  inputValue.value = '';
}

function handleKey(e: KeyboardEvent) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendMessage();
  }
}
</script>
