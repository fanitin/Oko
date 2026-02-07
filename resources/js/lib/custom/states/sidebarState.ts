import { reactive } from 'vue';

export const sidebarState = reactive({
    activeChatId: 0,
    chats: [] as any[],
    onlineUsers: [] as number[],

    updateFromMessage(sidebar: any, myUserId: number) {
        const chat = this.chats.find((c) => c.id === sidebar.chatId);
        if (!chat) return;

        chat.lastMessage = sidebar.lastMessage;

        if (this.activeChatId !== chat.id && sidebar.senderId !== myUserId) {
            chat.unreadCount = (chat.unreadCount ?? 0) + sidebar.unreadCountIncrement;
        }

        sortChats(this.chats);
    },

    markAsRead(chatId: number) {
        const chat = this.chats.find((c) => c.id === chatId);
        if (chat) chat.unreadCount = 0;
    },

    deletedMessage(messageId: number, sidebar: any) {
        if (!messageId) return;

        const chat = this.chats.find((c) => c.id === sidebar.chatId);
        if (!chat) return;

        chat.lastMessage = sidebar.lastMessage;

        if (this.activeChatId !== chat.id) {
            chat.unreadCount = chat.unreadCount > 0 ? (chat.unreadCount ?? 0) - sidebar.unreadCountDecrement : 0;
        }
        sortChats(this.chats);
    },

    resetUnreadCount(chatId: number) {
        const chat = this.chats.find((c) => c.id === chatId);
        if (!chat) return;

        chat.unreadCount = 0;
    },

    updateSeenStatus(data: any, userId: number) {
        if (data.userId === userId) return;
        const chat = this.chats.find((c) => c.id === data.chatId);
        if (!chat || !chat.lastMessage) return;

        chat.lastMessage.status = 'seen';
    },

    editedMessage(sidebar: any) {
        const chat = this.chats.find((c) => c.id === sidebar.chatId);
        if (!chat) return;

        if (sidebar.lastMessage) {
            chat.lastMessage = sidebar.lastMessage;
        }
    },

    setOnlineUsers(users: number[]) {
        this.onlineUsers = [...users];
    },

    addOnlineUser(id: number) {
        if (!this.onlineUsers.includes(id)) {
            this.onlineUsers = [...this.onlineUsers, id];
        }
    },

    removeOnlineUser(id: number) {
        this.onlineUsers = this.onlineUsers.filter((u) => u !== id);
    },

    togglePin(chatId: number) {
        this.chats.find((c) => c.id === chatId).settings.isPinned = !this.chats.find((c) => c.id === chatId).settings.isPinned;
        sortChats(this.chats);
    },

    toggleMute(chatId: number) {
        this.chats.find((c) => c.id === chatId).settings.isMuted = !this.chats.find((c) => c.id === chatId).settings.isMuted;
    },

    checkIfChatAddedToSidebar(chatId: number, header: string, avatar: string, type: string) {
        if (this.chats.some((c) => c.id === chatId)) return;

        this.chats.unshift({
            id: chatId,
            avatar: avatar,
            name: header,
            type: type,
            settings: {
                isPinned: false,
                isMuted: false,
            },
            unreadCount: 0,
        });

        sortChats(this.chats);
    },
});

function sortChats(chats: any[]) {
    chats.sort((a, b) => {
        if (a.settings.isPinned !== b.settings.isPinned) {
            return a.settings.isPinned ? -1 : 1;
        }

        const aTime = a.lastMessage?.created_at ? new Date(a.lastMessage.created_at).getTime() : new Date(a.createdAt || 0).getTime();

        const bTime = b.lastMessage?.created_at ? new Date(b.lastMessage.created_at).getTime() : new Date(b.createdAt || 0).getTime();

        return bTime - aTime;
    });
}
