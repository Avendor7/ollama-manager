<script setup lang="ts">
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarTrigger
} from '@/components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import ChatSessions from '@/components/ChatSessions.vue';

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <div class="flex items-center justify-between">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <AppLogo />
                </Link>
                <SidebarTrigger/>
            </div>
        </SidebarHeader>

        <SidebarContent>
            <ChatSessions />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
