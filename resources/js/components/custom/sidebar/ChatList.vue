<script setup lang="ts">
import { computed, ref } from 'vue';
import { sidebarState } from '@/lib/custom/states/sidebarState';
import ChatItem from './ChatItem.vue';
import { usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { MessageSquare, SearchX } from 'lucide-vue-next';

const page = usePage();
const user = ref(page.props.auth.user);

if (!sidebarState.chats.length) {
    sidebarState.chats = page.props.chats.map((c: any) => ({
        ...c,
        unreadCount: c.unreadCount ?? 0,
    }));
}

const chats = computed(() => sidebarState.chats);

const selectChat = (chatId: number) => {
    sidebarState.activeChatId = chatId;
};

useEcho(`user.${user.value.id}`, '.message.sent', (e: any) => {
    sidebarState.updateFromMessage(e.sidebar, user.value.id);
});

useEcho(`user.${user.value.id}`, '.message.deleted', (e: any) => {
    sidebarState.deletedMessage(e.messageId, e.sidebar);
});

useEcho(`user.${user.value.id}`, '.message.markAsRead', (e: any) => {
    sidebarState.updateSeenStatus(e, user.value.id);
});

useEcho(`user.${user.value.id}`, '.message.edited', (e: any) => {
    sidebarState.editedMessage(e.sidebar);
});

useEcho(`user.${user.value.id}`, '.chat.deleted', (e: any) => {
    sidebarState.removeChat(e.chatID);
});

useEcho(`user.${user.value.id}`, '.chat.created', (e: any) => {
    const chat = {
        ...e.chat,
        unreadCount: 0
    }
    sidebarState.createChat(chat);
});
</script>

<template>
    <!-- Search results info -->
    <div v-if="sidebarState.isSearching && chats.length > 0" class="px-4 py-2 text-xs text-gray-600 dark:text-gray-400">
        Found {{ chats.length }} chat{{ chats.length !== 1 ? 's' : '' }}
    </div>

    <div v-if="chats.length > 0" class="flex h-full flex-col gap-2 overflow-y-auto p-2">
        <ChatItem
            v-for="chat in chats"
            :key="chat.id"
            :chat="chat"
            :class="chat.id === sidebarState.activeChatId ? 'rounded-xl bg-gray-200 dark:bg-gray-700' : ''"
            @click="selectChat(chat.id)"
        />
    </div>

    <!-- No results from search -->
    <div v-else-if="sidebarState.isSearching" class="flex h-full flex-col items-center justify-center p-6">
        <div class="relative mb-6">
            <div class="absolute inset-0 animate-pulse rounded-full bg-gray-500/10 dark:bg-gray-500/5 blur-3xl"></div>
            <div class="relative rounded-full bg-gradient-to-br from-gray-400 via-gray-500 to-gray-600 p-5 shadow-xl">
                <SearchX class="h-14 w-14 text-white" stroke-width="1.5" />
            </div>
        </div>

        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            No chats found
        </h3>

        <p class="max-w-xs text-center text-sm text-gray-600 dark:text-gray-400">
            Try searching with different keywords
        </p>
    </div>

    <!-- No chats at all -->
    <div v-else class="flex h-full flex-col items-center justify-center p-6">
        <div class="relative mb-6">
            <div class="absolute inset-0 animate-pulse rounded-full bg-purple-500/10 dark:bg-purple-500/5 blur-3xl"></div>
            <div class="relative rounded-full bg-gradient-to-br from-purple-500 via-violet-500 to-fuchsia-500 p-5 shadow-xl">
                <MessageSquare class="h-14 w-14 text-white" stroke-width="1.5" />
            </div>
        </div>

        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
            No chats yet
        </h3>

        <p class="max-w-xs text-center text-sm text-gray-600 dark:text-gray-400">
            Start a new conversation and it will appear here
        </p>
    </div>
</template>

<style scoped>
div::-webkit-scrollbar {
    width: 6px;
}

div::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}

div::-webkit-scrollbar-track {
    background: transparent;
}
</style>
