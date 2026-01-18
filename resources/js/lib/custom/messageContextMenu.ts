import { reactive } from 'vue'

export const messageContextMenu = reactive({
    open: false,
    x: 0,
    y: 0,

    message: null as null | {
        id: number
        body: string
        isFromMe: boolean
        user: {
            username: string
        }
    },
})
