<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { MoreVertical, Bookmark } from 'lucide-vue-next';
import { computed } from 'vue';
import { sidebarState } from '@/lib/custom/states/sidebarState';
import { chatContextMenu } from '@/lib/custom/chatContextMenu';

const props = defineProps<{
    chat: {
        id: number;
        type: 'self' | 'private' | 'group';
        settings: {
            isPinned: boolean;
            isMuted: boolean;
        };
        header: {
            title: string;
            avatar: string | null;
            friendUserId?: number;
            link: {
                route: string;
                params: any[];
            };
        };
    };
}>();

const safeHeader = computed(() => ({
    title: props.chat.header.title,
    avatar: props.chat.header.avatar,
    link: props.chat.header.link,
}));

const firstLetter = computed(() => safeHeader.value.title?.charAt(0).toUpperCase() ?? '?');

const isOnline = computed(() => {
    if (!props.chat.header.friendUserId) return false;
    return sidebarState.onlineUsers.includes(props.chat.header.friendUserId);
});

const isSelfChat = computed(() => props.chat.type === 'self');

function onMenuClick(e: MouseEvent) {
    if(chatContextMenu.open){
        chatContextMenu.open = false;
        return;
    }

    const menuWidth = 192;
    const menuHeight = 140;
    let x = e.clientX;

    let y = e.clientY;
    if (x + menuWidth > window.innerWidth) {

        x = window.innerWidth - menuWidth - 5;
    }
    if (y + menuHeight > window.innerHeight) {

        y = window.innerHeight - menuHeight - 5;
    }
    chatContextMenu.open = true;

    chatContextMenu.x = x;
    chatContextMenu.y = y;
    chatContextMenu.chat = {
        id: props.chat.id,
        type: props.chat.type,
        isPinned: chatContextMenu.chat?.isPinned || props.chat.settings.isPinned,
        isMuted: chatContextMenu.chat?.isMuted || props.chat.settings.isMuted,
    };
}
</script>

<template>
    <div class="flex items-center justify-between border-b-2 border-gray-300 bg-white/80 backdrop-blur-sm p-2 shadow-sm dark:bg-gray-900/80 dark:border-gray-700">
        <Link :href="route(safeHeader.link.route, safeHeader.link.params)">
            <div class="flex cursor-pointer items-center gap-2">
                <div class="relative">
                    <div
                        v-if="isSelfChat"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-green-400 to-green-600 dark:from-purple-600 dark:to-fuchsia-600"
                    >
                        <Bookmark class="h-5 w-5 text-white" fill="white" />
                    </div>
                    <img
                        v-else-if="safeHeader.avatar"
                        :src="safeHeader.avatar"
                        alt="avatar"
                        class="h-10 w-10 rounded-full border-2 border-gray-300 object-cover shadow-sm dark:border-gray-700"
                    />
                    <div v-else class="flex h-10 w-10 items-center justify-center rounded-full shadow-sm bg-green-100 text-green-700 dark:bg-purple-800/50 dark:text-purple-300">
                        {{ getInitials(chatName) }}
                    </div>

                    <span
                        v-if="isOnline && !isSelfChat"
                        class="absolute right-0 bottom-0 h-3 w-3 rounded-full border-2 border-white bg-green-500 dark:border-gray-900"
                    />
                </div>

                <span class="font-medium text-gray-900 dark:text-gray-100">
                    {{ safeHeader.title }}
                </span>
            </div>
        </Link>

        <div class="flex items-center gap-3">
            <button @click.stop="onMenuClick($event)" class="rounded-md p-2 transition hover:bg-gray-200 dark:hover:bg-gray-700">
                <MoreVertical class="h-5 w-5 text-gray-700 dark:text-gray-300" />
            </button>
        </div>
    </div>
</template>
