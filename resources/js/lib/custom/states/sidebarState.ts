import { reactive } from 'vue'

export const sidebarState = reactive({
    activeChatId: 0,
    chats: [] as any[],

    updateFromMessage(sidebar: any, myUserId: number) {
        const chat = this.chats.find(c => c.id === sidebar.chatId)
        if (!chat) return

        chat.lastMessage = sidebar.lastMessage;

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
    },

    deletedMessage(messageId: number, sidebar: any) {
        if(!messageId) return;

        const chat = this.chats.find(c => c.id === sidebar.chatId)
        if (!chat) return;

        chat.lastMessage = sidebar.lastMessage;

        if(this.activeChatId !== chat.id) {
            chat.unreadCount = chat.unreadCount > 0 ? ((chat.unreadCount ?? 0) - sidebar.unreadCountDecrement) : 0;
        }
    },

    resetUnreadCount(chatId: number) {
        const chat = this.chats.find(c => c.id === chatId)
        if (!chat) return;

        chat.unreadCount = 0
    },

    updateSeenStatus(data: any, userId: number) {
        if(data.userId === userId) return;
        const chat = this.chats.find(c => c.id === data.chatId)
        if (!chat || !chat.lastMessage) return;

        chat.lastMessage.status = 'seen';
    },

    editedMessage(sidebar: any){
        const chat = this.chats.find(c => c.id === sidebar.chatId)
        if (!chat) return;

        if(sidebar.lastMessage){
            chat.lastMessage = sidebar.lastMessage;
        }
    },
})
