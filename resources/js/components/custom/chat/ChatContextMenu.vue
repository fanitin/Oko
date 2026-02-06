<script setup lang="ts">
import { chatContextMenu } from '@/lib/custom/chatContextMenu'
import { Pin, BellOff, Trash2 } from 'lucide-vue-next'
import axios from 'axios'

function close() {
    chatContextMenu.open = false
}

async function pin() {
    try{
        const res = await axios.get(route('chat.toggle-pin', { chat: chatContextMenu.chat?.id }))
        console.log(res.data)
    }catch (e){

    }

    close()
}

async function mute() {
    console.log('mute chat', chatContextMenu.chat?.id)
    close()
}

async function remove() {
    if (!confirm('Delete chat permanently?')) return
    console.log('delete chat', chatContextMenu.chat?.id)
    close()
}
</script>

<template>
    <div
        v-if="chatContextMenu.open"
        class="fixed z-50 bg-white dark:bg-gray-800 shadow-xl rounded-lg py-1 w-48"
        :style="{ top: chatContextMenu.y + 'px', left: chatContextMenu.x + 'px' }"
        v-click-outside="{ handler: close }"
    >
        <button class="menu-item" @click="pin">
            <Pin class="w-4 h-4 mr-2" />
            {{ chatContextMenu.chat?.isPinned ? 'Unpin chat' : 'Pin chat'}}
        </button>

        <button class="menu-item" @click="mute" v-if="chatContextMenu.chat?.type !== 'self'">
            <BellOff class="w-4 h-4 mr-2" />
            {{ chatContextMenu.chat?.isMuted ? 'Unmute chat' : 'Mute chat'}}
        </button>

        <hr class="my-1 dark:border-gray-700" />

        <button
            class="menu-item text-red-500 hover:!bg-red-50 dark:hover:!bg-red-900/20"
            @click="remove"
        >
            <Trash2 class="w-4 h-4 mr-2" />
            Delete chat
        </button>
    </div>
</template>

<style scoped>
.menu-item {
    display: flex;
    align-items: center;
    width: 100%;
    text-align: left;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    background: transparent;
    border: none;
    cursor: pointer;
}
.menu-item:hover {
    background-color: rgba(0,0,0,0.05);
}
.dark .menu-item:hover {
    background-color: rgba(255,255,255,0.05);
}
</style>
