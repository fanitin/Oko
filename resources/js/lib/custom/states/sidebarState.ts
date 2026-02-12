import { reactive } from 'vue';

export const sidebarState = reactive({
    activeChatId: 0,
    chats: [] as any[],
    allChats: [] as any[],
    onlineUsers: [] as number[],
    searchQuery: '',
    isSearching: false,

    updateFromMessage(sidebar: any, myUserId: number) {
        const chat = this.chats.find((c) => c.id === sidebar.chatId);
        if (!chat) return;

        chat.lastMessage = sidebar.lastMessage;

        if (this.activeChatId !== chat.id && sidebar.senderId !== myUserId) {
            chat.unreadCount = (chat.unreadCount ?? 0) + sidebar.unreadCountIncrement;
        }

        sortChats(this.chats);

        if (this.isSearching) {
            const allChat = this.allChats.find((c) => c.id === sidebar.chatId);
            if (allChat) {
                allChat.lastMessage = sidebar.lastMessage;
                if (this.activeChatId !== allChat.id && sidebar.senderId !== myUserId) {
                    allChat.unreadCount = (allChat.unreadCount ?? 0) + sidebar.unreadCountIncrement;
                }
                sortChats(this.allChats);
            }
        }
    },

    markAsRead(chatId: number) {
        const chat = this.chats.find((c) => c.id === chatId);
        if (chat) chat.unreadCount = 0;

        if (this.isSearching) {
            const allChat = this.allChats.find((c) => c.id === chatId);
            if (allChat) allChat.unreadCount = 0;
        }
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

        if (this.isSearching) {
            const allChat = this.allChats.find((c) => c.id === sidebar.chatId);
            if (allChat) {
                allChat.lastMessage = sidebar.lastMessage;
                if (this.activeChatId !== allChat.id) {
                    allChat.unreadCount = allChat.unreadCount > 0 ? (allChat.unreadCount ?? 0) - sidebar.unreadCountDecrement : 0;
                }
                sortChats(this.allChats);
            }
        }
    },

    resetUnreadCount(chatId: number) {
        const chat = this.chats.find((c) => c.id === chatId);
        if (!chat) return;

        chat.unreadCount = 0;

        if (this.isSearching) {
            const allChat = this.allChats.find((c) => c.id === chatId);
            if (allChat) allChat.unreadCount = 0;
        }
    },

    updateSeenStatus(data: any, userId: number) {
        if (data.userId === userId) return;
        const chat = this.chats.find((c) => c.id === data.chatId);
        if (!chat || !chat.lastMessage) return;

        chat.lastMessage.status = 'seen';

        if (this.isSearching) {
            const allChat = this.allChats.find((c) => c.id === data.chatId);
            if (allChat && allChat.lastMessage) {
                allChat.lastMessage.status = 'seen';
            }
        }
    },

    editedMessage(sidebar: any) {
        const chat = this.chats.find((c) => c.id === sidebar.chatId);
        if (!chat) return;

        if (sidebar.lastMessage) {
            chat.lastMessage = sidebar.lastMessage;
        }

        if (this.isSearching) {
            const allChat = this.allChats.find((c) => c.id === sidebar.chatId);
            if (allChat && sidebar.lastMessage) {
                allChat.lastMessage = sidebar.lastMessage;
            }
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
        const chat = this.chats.find((c) => c.id === chatId);
        if (chat) {
            chat.settings.isPinned = !chat.settings.isPinned;
            sortChats(this.chats);
        }

        if (this.isSearching) {
            const allChat = this.allChats.find((c) => c.id === chatId);
            if (allChat) {
                allChat.settings.isPinned = !allChat.settings.isPinned;
                sortChats(this.allChats);
            }
        }
    },

    toggleMute(chatId: number) {
        const chat = this.chats.find((c) => c.id === chatId);
        if (chat) {
            chat.settings.isMuted = !chat.settings.isMuted;
        }

        if (this.isSearching) {
            const allChat = this.allChats.find((c) => c.id === chatId);
            if (allChat) {
                allChat.settings.isMuted = !allChat.settings.isMuted;
            }
        }
    },

    removeChat(chatId: number) {
        this.chats = this.chats.filter((c) => c.id !== chatId);

        if (this.isSearching) {
            this.allChats = this.allChats.filter((c) => c.id !== chatId);
        }
    },

    createChat(chat: any){
        if(this.chats.some((c) => c.id === chat.id)) return;
        this.chats = [
            ...this.chats,
            chat
        ];
        sortChats(this.chats);

        if (this.isSearching && !this.allChats.some((c) => c.id === chat.id)) {
            this.allChats = [...this.allChats, chat];
            sortChats(this.allChats);
        }
    },

    filterChats(query: string) {
        query = query.trim();
        this.searchQuery = query;

        if (!query) {
            this.clearSearch();
            return;
        }

        if (!this.isSearching) {
            this.allChats = [...this.chats];
            this.isSearching = true;
        }

        const lowerQuery = query.toLowerCase();

        this.chats = this.allChats.filter((chat) => {
            const chatName = chat.isGroup
                ? chat.name
                : chat.participants.find((p: any) => p.id !== chat.currentUserId)?.name || 'Unknown';

            if (chatName.toLowerCase().includes(lowerQuery)) {
                return true;
            }

            if (chat.isGroup && chat.participants.some((p: any) =>
                p.name?.toLowerCase().includes(lowerQuery)
            )) {
                return true;
            }

            return !!chat.lastMessage?.body?.toLowerCase().includes(lowerQuery);
        });
    },

    clearSearch() {
        if (this.isSearching) {
            this.chats = [...this.allChats];
            this.allChats = [];
        }
        this.searchQuery = '';
        this.isSearching = false;
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
