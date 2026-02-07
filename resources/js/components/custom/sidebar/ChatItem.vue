<script setup lang="ts">
import { useSidebar } from '@/components/ui/sidebar';
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Check, CheckCheck } from 'lucide-vue-next';
import { format, isToday, isThisWeek } from 'date-fns';
import { sidebarState } from '@/lib/custom/states/sidebarState';
import { Pin, BellOff } from 'lucide-vue-next';


const { state } = useSidebar();
const props = defineProps<{
    chat: {
        id: number
        name?: string
        lastMessage?: {
            id: number
            user_id: number
            chat_id: number
            body: string | null
            status: 'delivered' | 'seen'
            created_at?: string
        } | null
        avatar?: string
        unreadCount?: number
        friendUserId?: number
        settings: {
            isPinned: boolean
            isMuted: boolean
        }
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
    },
    settings: {
        isPinned: props.chat.settings?.isPinned ?? false,
        isMuted: props.chat.settings?.isMuted ?? false,
    },
    avatar: props.chat.avatar ?? null,
    unreadCount: (props.chat.unreadCount && props.chat.unreadCount > 99 ? '99+' : props.chat.unreadCount) ?? 0,
}));

const firstLetter = computed(() => (safeChat.value.name ? safeChat.value.name.charAt(0) : '?'));

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
    if(!props.chat.friendUserId) return false;
    return sidebarState.onlineUsers.includes(props.chat.friendUserId) && props.chat.friendUserId !== page.props.auth.user.id;
});
</script>

<template>
    <Link :href="route('chat.show', safeChat.id)">
        <div
            :class="[
                'flex cursor-pointer items-center rounded-xl transition hover:bg-gray-200 dark:hover:bg-gray-800 gap-2 p-1',
            ]"
        >
            <div class="relative">
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
                <span
                    v-if="isOnline"
                    class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-green-500 border-2 border-white dark:border-gray-900"
                />
            </div>

            <div
                class="flex-1 overflow-hidden transition-all duration-300"
                :class="{
                    'max-w-0 opacity-0': isCollapsed,
                    'max-w-full opacity-100': !isCollapsed,
                }"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="truncate font-medium text-gray-900 dark:text-gray-100 flex-1">{{ safeChat.name }}</span>
                    <div class="flex items-center gap-1">
                        <div
                            v-if="safeChat.settings.isPinned"
                            class="rounded-full bg-blue-500 p-0.5"
                        >
                            <Pin class="h-2.5 w-2.5 text-white" />
                        </div>
                        <div
                            v-if="safeChat.settings.isMuted"
                            class="rounded-full bg-gray-500 p-0.5"
                        >
                            <BellOff class="h-2.5 w-2.5 text-white" />
                        </div>
                        <span
                            v-if="safeChat.unreadCount"
                            :class="[
                                'rounded-full px-2 py-0.5 text-xs text-white',
                                safeChat.settings.isMuted ? 'bg-gray-400' : 'bg-blue-500'
                            ]"
                        >
                            {{ safeChat.unreadCount }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-2">
                    <p class="truncate text-xs text-gray-700 dark:text-gray-400 flex-1">
                        {{ safeChat.lastMessage?.body }}
                    </p>
                    <div class="flex items-center gap-1 ml-2 min-w-[70px] justify-end">
                        <span v-if="isFromMe">
                            <CheckCheck v-if="safeChat.lastMessage?.status === 'seen'" class="h-4 w-4 text-cyan-300" />
                            <Check v-else-if="safeChat.lastMessage?.status === 'delivered'" class="h-4 w-4" />
                        </span>
                        <span class="text-xs text-gray-400">
                            {{ lastMessageTime }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </Link>
</template>


<style scoped>
.flex-1 {
    transition: max-width 0.3s ease, opacity 0.3s ease;
}
.min-w-\[70px\] {
    min-width: 70px;
}
</style>
