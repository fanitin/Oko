<script setup lang="ts">
import { computed, watch } from 'vue';
import { sidebarState } from '@/lib/custom/sidebarState';
import ChatItem from './ChatItem.vue'
import { usePage } from '@inertiajs/vue3';
const page = usePage()

const chats = computed(() => page.props.chats ?? [])

const selectChat = (chatId: number) => {
    sidebarState.activeChatId = chatId
}

watch(
    () => page.url,
    (url) => {
        const match = url.match(/^\/chat\/(\d+)/)

        sidebarState.activeChatId = match
            ? Number(match[1])
            : 0
    },
    { immediate: true }
)
</script>

<template>
    <div class="flex flex-col gap-2 overflow-y-auto h-full p-2">
        <ChatItem
            v-for="chat in chats"
            :key="chat.id"
            :chat="chat"
            :class="chat.id === sidebarState.activeChatId ? 'bg-gray-200 dark:bg-gray-700 rounded-xl' : ''"
            @click="selectChat(chat.id)"
        />
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
