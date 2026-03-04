export interface MediaItem {
    id: number
    type: 'image' | 'video' | 'file'
    path: string
    meta: {
        original_name?: string
        size?: number
        mime_type?: string
    }
}

export interface MessageType {
    id: number
    body: string
    edited_at?: string | null
    isFromMe: boolean
    status?: 'delivered' | 'seen' | null
    created_at: string
    reply_to?: {
        id: number
        body: string
        user: {
            id: number
            username: string
        }
    }
    user: {
        id: number
        username: string
        avatar: string | null
    }
    media?: MediaItem[]
}
