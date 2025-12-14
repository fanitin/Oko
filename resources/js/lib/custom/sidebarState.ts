import { reactive } from 'vue'

export const sidebarState = reactive({
    activeChatId: 0,
    chats: [] as Array<{
        id: number
        name?: string
        lastMessage?: string
        avatar?: string
        unreadCount?: number
        participants: number[]
    }>
})
