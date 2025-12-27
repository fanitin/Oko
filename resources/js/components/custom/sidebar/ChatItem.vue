<script setup lang="ts">
import { useSidebar } from '@/components/ui/sidebar';
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const { state } = useSidebar();

const props = defineProps<{
    chat: {
        id: number
        name?: string
        lastMessage?: string
        avatar?: string
        unreadCount?: number
    };
}>();

const isCollapsed = computed(() => state.value === 'collapsed');

const safeChat = computed(() => ({
    id: props.chat.id ?? 0,
    name: props.chat.name ?? 'Unknown user',
    lastMessage: props.chat.lastMessage ?? 'No messages yet',
    avatar: props.chat.avatar ?? null,
    unreadCount: props.chat.unreadCount ?? 0,
}));

const firstLetter = computed(() => (safeChat.value.name ? safeChat.value.name.charAt(0) : '?'));
</script>

<template>
    <Link :href="route('chat.show', safeChat.id)">
        <div
            :class="[
                'flex cursor-pointer items-center rounded-xl transition hover:bg-gray-200 dark:hover:bg-gray-800 gap-2 p-1',
            ]"
        >
            <img
                v-if="safeChat.avatar"
                :src="safeChat.avatar"
                alt="avatar"
                class="rounded-full border-2 border-gray-300 object-cover transition-all duration-300 dark:border-gray-700 h-10 w-10"
            />
            <div
                v-else
                class="flex items-center justify-center rounded-full bg-gray-300 transition-all duration-300 dark:bg-gray-600 h-10 w-10"
            >
                <span class="font-bold text-gray-600 dark:text-gray-300">{{ firstLetter }}</span>
            </div>

            <div
                class="flex-1 overflow-hidden transition-all duration-300"
                :class="{
                    'max-w-0 opacity-0': isCollapsed,
                    'max-w-full opacity-100': !isCollapsed,
                }"
            >
                <div class="flex items-center justify-between">
                    <span class="truncate font-medium text-gray-900 dark:text-gray-100">{{ safeChat.name }}</span>
                    <span
                        v-if="safeChat.unreadCount"
                        class="rounded-full bg-blue-500 px-2 py-0.5 text-xs text-white"
                    >
                        {{ safeChat.unreadCount }}
                    </span>
                </div>
                <p class="truncate text-sm text-gray-700 dark:text-gray-400">
                    {{ safeChat.lastMessage }}
                </p>
            </div>
        </div>
    </Link>
</template>

<style scoped>
.flex-1 {
    transition: max-width 0.3s ease, opacity 0.3s ease;
}
</style>
