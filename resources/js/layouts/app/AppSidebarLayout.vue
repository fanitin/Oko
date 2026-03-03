<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import type { BreadcrumbItemType } from '@/types';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { onMounted, onUnmounted, watch } from 'vue';
import { useEchoPresence } from '@laravel/echo-vue';
import { sidebarState } from '@/lib/custom/states/sidebarState';
import { useNotifications } from '@/composables/useNotifications';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { channel, leave } = useEchoPresence('online');
const { resetTitle } = useNotifications();

const handleFocus = () => {
    if (sidebarState.activeChatId) {
        resetTitle();
    }
};

onMounted(() => {
    const presence = channel();

    presence.here((users: any[]) => {
        sidebarState.setOnlineUsers(users.map((u) => u.id));
    });

    presence.joining((user: any) => {
        sidebarState.addOnlineUser(user.id);
    });

    presence.leaving((user: any) => {
        sidebarState.removeOnlineUser(user.id);
    });

    window.addEventListener('focus', handleFocus);
});

onUnmounted(() => {
    leave();
    window.removeEventListener('focus', handleFocus);
});

watch(() => sidebarState.activeChatId, () => {
    if (document.hasFocus()) {
        resetTitle();
    }
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <div class="lg:hidden p-2 border-b border-gray-200 dark:border-gray-800">
                <SidebarTrigger />
            </div>
            <slot />
        </AppContent>
    </AppShell>
</template>
