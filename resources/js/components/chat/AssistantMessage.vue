<template>
  <div class="flex justify-start">
    <div class="flex items-end gap-2">
      <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-green-400 to-blue-600 text-lg font-bold text-white">
        O
      </div>
      <div class="max-w-[70%] rounded-xl bg-zinc-200 px-4 py-2 whitespace-pre-line text-zinc-900 shadow-md dark:bg-zinc-800 dark:text-zinc-100">
        {{ content }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  content: string;
}>();
</script>
