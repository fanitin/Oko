<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useSidebar } from '@/components/ui/sidebar';
import { computed } from 'vue';

const { state } = useSidebar();
const isCollapsed = computed(() => state.value === 'collapsed');
</script>

<template>
    <div class="flex flex-col gap-3 p-2">
        <Link
            :href="route('home')"
            :class="[
                'flex items-center gap-3 transition-all duration-300',
                isCollapsed ? 'justify-center' : ''
            ]"
        >
            <img
                src="/images/logo/oko.png"
                alt="OKO"
                class="h-10 w-10 flex-shrink-0 rounded-lg object-contain hover:scale-125 transition-transform duration-300"
            />
            <span
                :class="[
                    'text-lg font-bold text-gray-900 transition-all duration-300 dark:text-gray-100',
                    isCollapsed ? 'hidden' : ''
                ]"
            >
                OKO
            </span>
        </Link>
    </div>
</template>

<style scoped>
</style>
