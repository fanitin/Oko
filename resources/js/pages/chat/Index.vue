<script setup lang="ts">
import MainPopup from '@/components/custom/MainPopup.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, nextTick } from 'vue';
import ChatNavBar from "@/components/custom/chat/ChatNavBar.vue";
import Emoji from '@/components/custom/chat/Emoji.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chat',                          //change later
        href: '/chat',
    },
];

const props = defineProps<{
    chat: {
        id: number
        type: 'self' | 'private' | 'group'
        header: {
            title: string
            avatar: string | null
            link: {
                route: string
                params: any[]
            }
        }
        participants: Array<{
            id: number
            username: string
        }>
    }
}>()

const popupRef = ref<typeof MainPopup>();

const message = ref("");
const messagesContainer = ref<HTMLElement|null>(null);

const inputRef = ref<HTMLInputElement | null>(null);

const handleEmojiSelect = (emoji: string) => {
    const input = inputRef.value;
    if (!input) return;

    const start = input.selectionStart || 0;
    const end = input.selectionEnd || 0;

    message.value =
        message.value.substring(0, start) +
        emoji +
        message.value.substring(end);

    nextTick(() => {
        input.focus();
        input.selectionStart = input.selectionEnd = start + emoji.length;
    });
};
</script>

<template>
    <Head :title="props.chat.header.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ChatNavBar :chat="props.chat"/>

        <div class="flex flex-col flex-1">

            <div
                ref="messagesContainer"
                class="flex-1 overflow-y-auto p-4 space-y-3
                   bg-gray-200 dark:bg-gray-950"
            >
<!--                <div v-for="msg in messages" :key="msg.id" class="flex"-->
<!--                     :class="msg.is_from_me ? 'justify-end' : 'justify-start'">-->

<!--                    <div-->
<!--                        class="max-w-xs px-4 py-2 rounded-lg shadow-sm"-->
<!--                        :class="msg.is_from_me-->
<!--                        ? 'bg-blue-500 dark:bg-purple-600 text-white rounded-br-none'-->
<!--                        : 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 rounded-bl-none'"-->
<!--                    >-->
<!--                        {{ msg.body }}-->
<!--                    </div>-->
<!--                </div>-->
            </div>

            <div class="border-t border-gray-300 dark:border-gray-700
                    p-3 bg-gray-100 dark:bg-gray-900">
                <form @submit.prevent="send">
                    <div class="flex items-center gap-3">
                        <Emoji @select="handleEmojiSelect" />

                        <input
                            ref="inputRef"
                            v-model="message"
                            type="text"
                            placeholder="Write a message..."
                            class="flex-1 px-4 py-2 rounded-lg border
                               border-gray-300 dark:border-gray-600
                               bg-white dark:bg-gray-800
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-blue-500 dark:focus:ring-purple-950
                               outline-none"
                        />

                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700
                               dark:bg-purple-800 dark:hover:bg-purple-900
                               text-white rounded-lg transition font-medium"
                        >
                            Send
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </AppLayout>

    <MainPopup ref="popupRef" />
</template>
