<script setup lang="ts">
import { messageContextMenu } from '@/lib/custom/messageContextMenu'
import { replyState } from '@/lib/custom/replyState'
import axios from 'axios';
import { sidebarState } from '@/lib/custom/sidebarState';
import { computed } from 'vue';
import { mainPopupState } from '@/lib/custom/mainPopupState';

const currChatId = computed(() => sidebarState.activeChatId)
function close() {
    messageContextMenu.open = false
}

function copy() {
    if (messageContextMenu.message?.body) {
        navigator.clipboard.writeText(messageContextMenu.message.body)
    }
    close()
}

function reply() {
    if (messageContextMenu.message) {
        replyState.message = messageContextMenu.message
    }
    close()
}

function edit() {
    // edit mode
    close()
}

function remove() {
        axios.delete(route('chat.messages.delete', {chat: currChatId.value, message: messageContextMenu.message?.id}))
            .then(() => {
                mainPopupState.show('Message deleted successfully.', 'success')
            });

    close()
}
</script>

<template>
    <div
        v-if="messageContextMenu.open"
        class="fixed z-50 bg-white dark:bg-gray-800 shadow-xl rounded-lg py-1 w-48"
        :style="{ top: messageContextMenu.y + 'px', left: messageContextMenu.x + 'px' }"
        v-click-outside="{ handler: close }"
    >
        <button class="menu-item" @click="reply">Reply</button>

        <button class="menu-item" @click="copy">Copy</button>

        <button
            v-if="messageContextMenu.message?.isFromMe"
            class="menu-item"
            @click="edit"
        >
            Edit
        </button>

        <button
            v-if="messageContextMenu.message?.isFromMe"
            class="menu-item text-red-500"
            @click="remove"
        >
            Delete
        </button>
    </div>
</template>

<style scoped>
.menu-item {
    width: 100%;
    text-align: left;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    color: inherit;
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
