import { reactive } from 'vue'

export const chatContextMenu = reactive({
    open: false,
    x: 0,
    y: 0,

    chat: null as null | {
        id: number
        type: string
        isPinned?: boolean
        isMuted?: boolean
    },
})
