<script setup lang="ts">
import { useSidebar } from '@/components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface User {
    id: number;
    username?: string;
    name?: string;
    email: string;
    main_avatar?: { path: string } | null;
}

const props = defineProps<{
    user: User;
}>();

const { state } = useSidebar();

const isCollapsed = computed(() => state.value === 'collapsed');
const displayName = computed(() => props.user.username || props.user.name || 'User');
const firstLetter = computed(() => displayName.value.charAt(0).toUpperCase());
</script>

<template>
    <Link :href="route('profile.index')">
        <div
            :class="[
                'flex cursor-pointer items-center rounded-xl transition hover:bg-gray-200 dark:hover:bg-gray-800 gap-3 p-2',
                isCollapsed ? 'justify-center' : ''
            ]"
        >
            <img
                v-if="user.main_avatar"
                :src="user.main_avatar.path"
                alt="avatar"
                class="h-12 w-12 flex-shrink-0 rounded-full border-2 border-gray-300 object-cover transition-all duration-300 dark:border-gray-700"
            />
            <div
                v-else
                class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-gray-400 to-gray-500 transition-all duration-300 dark:from-gray-600 dark:to-gray-700"
            >
                <span class="text-lg font-bold text-white">{{ firstLetter }}</span>
            </div>

            <div
                :class="[
                    'flex flex-col overflow-hidden transition-all duration-300',
                    isCollapsed ? 'max-w-0 opacity-0' : 'flex-1 max-w-full opacity-100'
                ]"
            >
                <span class="truncate text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ displayName }}
                </span>
                <span class="truncate text-xs text-gray-500 dark:text-gray-400">
                    {{ user.email }}
                </span>
            </div>
        </div>
    </Link>
</template>

<style scoped>
</style>
