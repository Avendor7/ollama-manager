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
                <div class="absolute inset-0 p-6">
                    <div class="mx-auto max-w-7xl">
                        <h1 class="mb-8 text-3xl font-bold tracking-tight">Running Models</h1>
                        
                        <div v-if="props.running.models.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div v-for="(model, index) in props.running.models" 
                                 :key="index"
                                 class="group relative overflow-hidden rounded-lg border bg-card p-6 shadow-sm transition-all hover:shadow-md">
                                <div class="flex flex-col gap-4">
                                    <!-- Header -->
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold">{{ model.name }}</h3>
                                            <p class="text-sm text-muted-foreground">{{ model.model }}</p>
                                        </div>
                                        <div class="rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                                            {{ model.details.parameter_size }}
                                        </div>
                                    </div>

                                    <!-- Details -->
                                    <div class="grid gap-2 text-sm">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">Format:</span>
                                            <span class="text-muted-foreground">{{ model.details.format }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">Family:</span>
                                            <span class="text-muted-foreground">{{ model.details.family }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">Quantization:</span>
                                            <span class="text-muted-foreground">{{ model.details.quantization_level }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">VRAM Usage:</span>
                                            <span class="text-muted-foreground">{{ (model.size_vram / 1024 / 1024).toFixed(2) }} GB</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">Size:</span>
                                            <span class="text-muted-foreground">{{ (model.size / 1024 / 1024).toFixed(2) }} GB</span>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span v-for="family in model.details.families" 
                                              :key="family"
                                              class="rounded-full bg-primary/20 px-2.5 py-1 text-xs font-medium text-white">
                                            {{ family }}
                                        </span>
                                    </div>

                                    <!-- Expiry -->
                                    <div class="mt-2 text-xs text-muted-foreground">
                                        Expires: {{ new Date(model.expires_at).toLocaleString() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="flex h-[200px] items-center justify-center rounded-lg border border-dashed">
                            <p class="text-muted-foreground">No running models</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
