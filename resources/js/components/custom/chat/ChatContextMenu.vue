<script setup lang="ts">
import { chatContextMenu } from '@/lib/custom/chatContextMenu';
import { sidebarState } from '@/lib/custom/states/sidebarState';
import axios from 'axios';
import { BellOff, Pin, PinOff, Bell, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

const pinLabel = computed(() => (chatContextMenu.chat?.isPinned ? 'Unpin chat' : 'Pin chat'));

const muteLabel = computed(() => (chatContextMenu.chat?.isMuted ? 'Unmute chat' : 'Mute chat'));

function close() {
    chatContextMenu.open = false;
}

async function pin() {
    if (!chatContextMenu.chat) return;

    try {
        const res = await axios.get(route('chat.toggle-pin', { chat: chatContextMenu.chat?.id }));
        if (res.data.status === 'success') {
            chatContextMenu.chat = {
                ...chatContextMenu.chat,
                isPinned: !chatContextMenu.chat.isPinned,
            };
            sidebarState.togglePin(chatContextMenu.chat.id);
        }
    } catch {}

    close();
}

async function mute() {
    if (!chatContextMenu.chat) return;

    try {
        const res = await axios.get(route('chat.toggle-mute', { chat: chatContextMenu.chat?.id }));

        if (res.data.status === 'success') {
            chatContextMenu.chat = {
                ...chatContextMenu.chat,
                isMuted: !chatContextMenu.chat.isMuted,
            };
            sidebarState.toggleMute(chatContextMenu.chat.id);
        }
    } catch {}

    close();
}

async function remove() {
    if (!confirm('Delete chat permanently?')) return;
    console.log('delete chat', chatContextMenu.chat?.id);
    close();
}
</script>

<template>
    <div
        v-if="chatContextMenu.open"
        class="fixed z-50 w-48 rounded-lg bg-white py-1 shadow-xl dark:bg-gray-800"
        :style="{ top: chatContextMenu.y + 'px', left: chatContextMenu.x + 'px' }"
        v-click-outside="{ handler: close }"
    >
        <button class="menu-item" @click="pin">
            <Pin v-if="!chatContextMenu.chat?.isPinned" class="mr-2 h-4 w-4" />
            <PinOff v-else class="mr-2 h-4 w-4" />
            {{ pinLabel }}
        </button>

        <button class="menu-item" @click="mute" v-if="chatContextMenu.chat?.type !== 'self'">
            <BellOff v-if="!chatContextMenu.chat?.isMuted" class="mr-2 h-4 w-4"/>
            <Bell v-else class="mr-2 h-4 w-4" />
            {{ muteLabel }}
        </button>

        <hr class="my-1 dark:border-gray-700" />

        <button class="menu-item text-red-500 hover:!bg-red-50 dark:hover:!bg-red-900/20" @click="remove">
            <Trash2 class="mr-2 h-4 w-4" />
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
    background-color: rgba(0, 0, 0, 0.05);
}

.dark .menu-item:hover {
    background-color: rgba(255, 255, 255, 0.05);
}
</style>
