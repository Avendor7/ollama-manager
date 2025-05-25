<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Status',
        href: '/status',
    },
];

import { RunningData } from '@/types/RunningModel';

const props = defineProps<{
    running: RunningData;
}>();
</script>

<template>
    <Head title="Status" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 rounded-xl border md:min-h-min">
                <PlaceholderPattern />
                <div class="absolute inset-0 flex items-center justify-center gap-4">
                    <h1 class="text-2xl font-bold">Status Page</h1>
                    <ul v-if="props.running.models.length > 0">
                        <li v-for="(model, index) in props.running.models" :key="index">
                            {{ model.name }} ({{ model.model }})
                        </li>
                    </ul>
                    <span v-else>No running models.</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
