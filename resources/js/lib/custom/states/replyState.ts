import { reactive } from 'vue'

interface ReplyMessage {
    id: number
    body: string
    isFromMe: boolean
    user: {
        username: string
    }
}

export const replyState = reactive({
    message: null as ReplyMessage | null,
})
