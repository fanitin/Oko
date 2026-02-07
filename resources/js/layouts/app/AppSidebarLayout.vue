<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import type { BreadcrumbItemType } from '@/types';
import { SidebarTrigger } from '@/components/ui/sidebar';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
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
