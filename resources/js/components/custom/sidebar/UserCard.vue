<script setup lang="ts">
import { useSidebar } from '@/components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface User {
    id: number;
    username: string;
    email: string;
    main_avatar?: { path: string } | null;
}

const props = defineProps<{
    user: User;
}>();

const { state } = useSidebar();

const isCollapsed = computed(() => state.value === 'collapsed');

const firstLetter = computed(() => (props.user.username ? props.user.username.charAt(0) : '?'));
</script>

<template>
    <Link :href="route('profile.index', user.id)">
        <div
            :class="[
                'flex cursor-pointer items-center rounded-xl transition hover:bg-gray-200 dark:hover:bg-gray-800',
                isCollapsed ? 'gap-2 p-1' : 'gap-3 p-2',
            ]"
        >
            <img
                v-if="user.main_avatar"
                :src="user.main_avatar.path"
                alt="avatar"
                :class="[
                    'rounded-full border-2 border-gray-300 object-cover transition-all duration-200 dark:border-gray-700',
                    isCollapsed ? 'h-8 w-8' : 'h-10 w-10',
                ]"
            />
            <div
                v-else
                :class="[
                    'flex items-center justify-center rounded-full bg-gray-300 transition-all duration-200 dark:bg-gray-700',
                    isCollapsed ? 'h-8 w-8' : 'h-10 w-10',
                ]"
            >
                <span class="font-bold text-gray-600 dark:text-gray-300">{{ firstLetter }}</span>
            </div>

            <transition name="fade">
                <div v-if="!isCollapsed" class="flex flex-1 flex-col overflow-hidden">
                    <span class="truncate text-sm font-semibold text-gray-900 md:text-base dark:text-gray-100">
                        {{ user.username }}
                    </span>
                </div>
            </transition>
        </div>
    </Link>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
