import { reactive } from 'vue'

export const sidebarState = reactive({
    activeChatId: 0,
    chats: [] as any[],
})

