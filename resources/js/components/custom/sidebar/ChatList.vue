<script setup lang="ts">
import { ref } from 'vue'
import ChatItem from './ChatItem.vue'

interface Chat {
    id: number
    name?: string
    lastMessage?: string
    avatar?: string
    unreadCount?: number
}

const chats = ref<Chat[]>([
    {
        id: 1,
        name: 'Виталий',
        lastMessage: 'Привет! Как дела?',
        avatar: 'https://i.pravatar.cc/40?u=1',
        unreadCount: 2,
    },
    {
        id: 2,
        name: 'Александр',
        lastMessage: 'Давай встретимся вечером',
        avatar: 'https://i.pravatar.cc/40?u=2',
        unreadCount: 0,
    },
    {
        id: 3,
        name: 'Мария',
        lastMessage: 'Я отправила тебе документы',
        avatar: undefined,
        unreadCount: 5,
    },
])

const activeChatId = ref<number | null>(null)

const selectChat = (chatId: number) => {
    activeChatId.value = chatId
}
</script>

<template>
    <div class="flex flex-col gap-2 overflow-y-auto h-full p-2">
        <ChatItem
            v-for="chat in chats"
            :key="chat.id"
            :chat="chat"
            :class="chat.id === activeChatId ? 'bg-gray-200 dark:bg-gray-700' : ''"
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
