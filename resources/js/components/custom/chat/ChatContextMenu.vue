<script setup lang="ts">
import Dialog from '@/components/custom/Dialog.vue';
import { chatContextMenu } from '@/lib/custom/chatContextMenu';
import { mainPopupState } from '@/lib/custom/states/mainPopupState';
import { sidebarState } from '@/lib/custom/states/sidebarState';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { Bell, BellOff, Pin, PinOff, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const showDeleteDialog = ref(false);
const isDeleting = ref(false);

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

function remove() {
    if (!chatContextMenu.chat) return;
    close();
    showDeleteDialog.value = true;
}

async function confirmDelete() {
    if (!chatContextMenu.chat) return;

    isDeleting.value = true;

    const chatId = chatContextMenu.chat.id;

    try {
        await axios.delete(route('chat.delete', { chat: chatId }));

        sidebarState.removeChat(chatId);

        if (sidebarState.activeChatId === chatId) {
            router.visit(route('home'));
        }

        showDeleteDialog.value = false;
        mainPopupState.show('Chat deleted successfully.', 'success');
    } catch (error: any) {
        const message = error.response?.data?.message ?? 'Chat has not been deleted.';
        showDeleteDialog.value = false;
        mainPopupState.show(message, 'error');
    } finally {
        isDeleting.value = false;
    }
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
            <BellOff v-if="!chatContextMenu.chat?.isMuted" class="mr-2 h-4 w-4" />
            <Bell v-else class="mr-2 h-4 w-4" />
            {{ muteLabel }}
        </button>

        <hr class="my-1 dark:border-gray-700" />

        <button
            class="menu-item text-red-500 hover:!bg-red-50 dark:hover:!bg-red-900/20"
            @click="remove"
            v-if="chatContextMenu.chat?.type !== 'self'"
        >
            <Trash2 class="mr-2 h-4 w-4" />
            Delete chat
        </button>
    </div>

    <Dialog
        v-model="showDeleteDialog"
        title="Delete chat?"
        description="This will permanently delete this chat from your account. This action cannot be undone."
        confirm-text="Delete"
        cancel-text="Cancel"
        variant="danger"
        :loading="isDeleting"
        @confirm="confirmDelete"
        @cancel="showDeleteDialog = false"
    />
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
