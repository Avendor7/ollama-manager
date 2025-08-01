<template>
  <div class="flex justify-end">
    <div class="max-w-[70%] rounded-xl bg-blue-500 px-4 py-2 whitespace-pre-line text-white shadow-md">
      {{ content }}
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  content: string;
}>();
</script>
