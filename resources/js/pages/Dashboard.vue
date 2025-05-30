<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>

    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col bg-white dark:bg-zinc-900 overflow-hidden">
            <!-- Chat Messages -->
            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-6 bg-zinc-50 dark:bg-zinc-900" id="chat-scroll">
                <!-- Example User Message -->
                <div class="flex justify-end">
                    <div class="max-w-[70%] bg-blue-500 text-white rounded-xl px-4 py-2 shadow-md">
                        Hi Ollama, can you help me with something?
                    </div>
                </div>
                <!-- Example Ollama Message -->
                <div class="flex justify-start">
                    <div class="flex items-end gap-2">
                        <div
                            class="w-8 h-8 rounded-full bg-gradient-to-br from-green-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg">
                            O</div>
                        <div
                            class="max-w-[70%] bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-xl px-4 py-2 shadow-md">
                            Of course! What do you need help with today?
                        </div>
                    </div>
                </div>
                <!-- Example User Message -->
                <div class="flex justify-end">
                    <div class="max-w-[70%] bg-blue-500 text-white rounded-xl px-4 py-2 shadow-md">
                        How do I run a model?
                    </div>
                </div>
                <!-- Example Ollama Message -->
                <div class="flex justify-start">
                    <div class="flex items-end gap-2">
                        <div
                            class="w-8 h-8 rounded-full bg-gradient-to-br from-green-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg">
                            O</div>
                        <div
                            class="max-w-[70%] bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-xl px-4 py-2 shadow-md">
                            To run a model, just type your prompt here and hit enter. I’ll take care of the rest!
                        </div>
                    </div>
                </div>
            </div>
            <!-- Chat Input -->
            <div
                class="border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-4 py-3 flex items-center gap-2">
                <input type="text" placeholder="Type your message..."
                    class="flex-1 bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                    disabled />
                <button
                    class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg px-4 py-2 font-semibold shadow transition disabled:opacity-60"
                    disabled>
                    Send
                </button>
            </div>
        </div>
    </AppLayout>
</template>
