<script setup lang="ts">
import { useSidebar } from '@/components/ui/sidebar';
import { sidebarState } from '@/lib/custom/states/sidebarState';
import { Link, usePage } from '@inertiajs/vue3';
import { format, isThisWeek, isToday } from 'date-fns';
import { BellOff, Bookmark, Check, CheckCheck, Pin, MessageSquare } from 'lucide-vue-next';
import { computed } from 'vue';

const { state } = useSidebar();
const props = defineProps<{
    chat: {
        id: number;
        name?: string;
        lastMessage?: {
            id: number;
            user_id: number;
            chat_id: number;
            body: string | null;
            status: 'delivered' | 'seen';
            created_at?: string;
            media?: any[];
        } | null;
        avatar?: string;
        unreadCount?: number;
        friendUserId?: number;
        settings: {
            isPinned: boolean;
            isMuted: boolean;
        };
        type: string;
    };
}>();

const page = usePage();

const isCollapsed = computed(() => state.value === 'collapsed');

const safeChat = computed(() => ({
    id: props.chat.id ?? 0,
    name: props.chat.name ?? 'Unknown user',
    lastMessage: {
        id: props.chat.lastMessage?.id ?? 0,
        user_id: props.chat.lastMessage?.user_id ?? 0,
        chat_id: props.chat.lastMessage?.chat_id ?? 0,
        body: props.chat.lastMessage?.body ?? 'No messages yet',
        created_at: props.chat.lastMessage?.created_at ?? null,
        status: props.chat.lastMessage?.status,
        media: props.chat.lastMessage?.media ?? [],
    },
    settings: {
        isPinned: props.chat.settings?.isPinned ?? false,
        isMuted: props.chat.settings?.isMuted ?? false,
    },
    avatar: props.chat.avatar ?? null,
    unreadCount: (props.chat.unreadCount && props.chat.unreadCount > 99 ? '99+' : props.chat.unreadCount) ?? 0,
    type: props.chat.type ?? 'private',
}));

const lastMessageTime = computed(() => {
    const created = safeChat.value.lastMessage?.created_at;
    if (!created) return '';
    const date = new Date(created);
    if (isToday(date)) {
        return format(date, 'HH:mm');
    }
    if (isThisWeek(date, { weekStartsOn: 1 })) {
        return format(date, 'EEEE');
    }
    return format(date, 'MMM d, yyyy');
});

const isFromMe = computed(() => {
    return safeChat.value.lastMessage?.user_id === page.props.auth.user.id;
});

const isOnline = computed(() => {
    if (!props.chat.friendUserId) return false;
    return sidebarState.onlineUsers.includes(props.chat.friendUserId) && props.chat.friendUserId !== page.props.auth.user.id;
});

const isSelfChat = computed(() => safeChat.value.type === 'self');

const getInitials = (name: string) => {
    if (!name) return '?';
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};
</script>

<template>
    <Link :href="route('chat.show', safeChat.id)">
        <div
            :class="[
                'flex cursor-pointer items-center gap-3 rounded-xl p-2 transition border border-transparent hover:border-gray-300 hover:bg-gray-100 dark:hover:border-gray-700 dark:hover:bg-gray-800',
                isCollapsed ? 'justify-center' : ''
            ]"
        >
            <div class="relative flex-shrink-0">
                <div
                    v-if="isSelfChat"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-green-400 to-green-600 dark:from-purple-600 dark:to-fuchsia-600"
                >
                    <Bookmark class="h-6 w-6 text-white" fill="white" />
                </div>
                <img
                    v-else-if="safeChat.avatar"
                    :src="safeChat.avatar"
                    alt="avatar"
                    class="h-12 w-12 rounded-full border-2 border-gray-300 object-cover shadow-sm transition-all duration-300 dark:border-gray-700"
                />
                <div v-else class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-700 dark:bg-purple-800/50 dark:text-purple-300 shadow-sm transition-all duration-300">
                    <span class="text-lg font-bold text-gray-700 dark:text-gray-300">
                        {{ getInitials(safeChat.name) }}
                    </span>
                </div>
                <span
                    v-if="isOnline && !isSelfChat"
                    class="absolute right-0 bottom-0 h-3.5 w-3.5 rounded-full border-2 border-white bg-green-500 dark:border-gray-900"
                />

                <span
                    v-if="isCollapsed && safeChat.unreadCount"
                    :class="[
                        'absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold text-white',
                        safeChat.settings.isMuted ? 'bg-gray-400' : 'bg-blue-500'
                    ]"
                >
                    {{ typeof safeChat.unreadCount === 'number' && safeChat.unreadCount > 9 ? '9+' : safeChat.unreadCount }}
                </span>
            </div>

            <div
                class="flex-1 overflow-hidden transition-all duration-300"
                :class="{
                    'max-w-0 opacity-0': isCollapsed,
                    'max-w-full opacity-100': !isCollapsed,
                }"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="flex-1 truncate text-base font-semibold text-gray-900 dark:text-gray-100">{{ safeChat.name }}</span>
                    <div class="flex items-center gap-1.5">
                        <div v-if="safeChat.settings.isPinned" class="rounded-full bg-blue-500 p-0.5">
                            <Pin class="h-3 w-3 text-white" />
                        </div>
                        <div v-if="safeChat.settings.isMuted" class="rounded-full bg-gray-500 p-0.5">
                            <BellOff class="h-3 w-3 text-white" />
                        </div>
                        <span
                            v-if="safeChat.unreadCount"
                            :class="['rounded-full px-2 py-0.5 text-xs font-medium text-white', safeChat.settings.isMuted ? 'bg-gray-400' : 'bg-blue-500']"
                        >
                            {{ safeChat.unreadCount }}
                        </span>
                    </div>
                </div>

                <div class="mt-1.5 flex items-center justify-between">
                    <p class="flex-1 truncate text-sm text-gray-600 dark:text-gray-400">
                        <template v-if="safeChat.lastMessage?.body && safeChat.lastMessage?.body.trim()">
                            {{ safeChat.lastMessage.body }}
                        </template>
                        <template v-else-if="safeChat.lastMessage && safeChat.lastMessage.media && safeChat.lastMessage.media.length > 0">
                            <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-400 font-bold">
                                <MessageSquare class="h-4 w-4" /> Media
                            </span>
                        </template>
                        <template v-else>
                            No messages yet
                        </template>
                    </p>
                    <div class="ml-2 flex min-w-[70px] items-center justify-end gap-1">
                        <span v-if="isFromMe">
                            <CheckCheck v-if="safeChat.lastMessage?.status === 'seen'" class="h-4 w-4 text-cyan-400" />
                            <Check v-else-if="safeChat.lastMessage?.status === 'delivered'" class="h-4 w-4 text-gray-400" />
                        </span>
                        <span class="text-xs text-gray-500">
                            {{ lastMessageTime }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </Link>
</template>
