import { reactive } from 'vue'

interface EditState {
    id: number
    body: string
    isFromMe: boolean
    user: {
        username: string
    }
}

export const editState = reactive({
    message: null as EditState | null,
})
