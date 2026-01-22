import { reactive } from 'vue'

export const sidebarState = reactive({
    activeChatId: 0,
    chats: [] as any[],

    updateFromMessage(sidebar: any, myUserId: number) {
        const chat = this.chats.find(c => c.id === sidebar.chatId)
        if (!chat) return

        chat.lastMessage = sidebar.lastMessage

        if (this.activeChatId !== chat.id && sidebar.senderId !== myUserId) {
            chat.unreadCount = (chat.unreadCount ?? 0) + sidebar.unreadCountIncrement
        }

        this.chats = [
            chat,
            ...this.chats.filter(c => c.id !== chat.id),
        ]
    },

    markAsRead(chatId: number) {
        const chat = this.chats.find(c => c.id === chatId)
        if (chat) chat.unreadCount = 0
    }
})

