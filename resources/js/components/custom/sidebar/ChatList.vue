<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { sidebarState } from '@/lib/custom/states/sidebarState';
import ChatItem from './ChatItem.vue'
import { usePage } from '@inertiajs/vue3'
import { useEcho, useEchoPresence } from '@laravel/echo-vue';

const page = usePage()
const user = ref(page.props.auth.user);

if (!sidebarState.chats.length) {
    sidebarState.chats = page.props.chats.map((c: any) => ({
        ...c,
        unreadCount: c.unreadCount ?? 0,
    }))
}

const chats = computed(() => sidebarState.chats)

const selectChat = (chatId: number) => {
    sidebarState.activeChatId = chatId
}

const {
    channel,
    leave,
} = useEchoPresence('online')

onMounted(() => {
    const presence = channel()

    presence.here((users: any[]) => {
        sidebarState.setOnlineUsers(users.map(u => u.id))
    })

    presence.joining((user: any) => {
        sidebarState.addOnlineUser(user.id)
    })

    presence.leaving((user: any) => {
        sidebarState.removeOnlineUser(user.id)
    })
})

onUnmounted(() => {
    leave()
})

useEcho(`user.${user.value.id}`, '.message.sent', (e: any) => {
    sidebarState.updateFromMessage(e.sidebar, user.value.id)
})

useEcho(`user.${user.value.id}`, '.message.deleted', (e: any) => {
    sidebarState.deletedMessage(e.messageId, e.sidebar)
})

useEcho(`user.${user.value.id}`, '.message.markAsRead', (e: any) => {
    sidebarState.updateSeenStatus(e, user.value.id)
})

useEcho(`user.${user.value.id}`, '.message.edited', (e: any) => {
    sidebarState.editedMessage(e.sidebar)
})
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
