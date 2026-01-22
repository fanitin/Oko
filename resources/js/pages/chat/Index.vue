<script setup lang="ts">
import ChatNavBar from '@/components/custom/chat/ChatNavBar.vue';
import Emoji from '@/components/custom/chat/Emoji.vue';
import MessagesList from '@/components/custom/chat/MessagesList.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { sidebarState } from '@/lib/custom/sidebarState';
import MessageContextMenu from "@/components/custom/chat/MessageContextMenu.vue";
import { replyState } from "@/lib/custom/replyState";
import ReplyPreview from '@/components/custom/chat/ReplyPreview.vue';
import { mainPopupState } from '@/lib/custom/mainPopupState';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chat', //change later
        href: '/chat',
    },
];

const props = defineProps<{
    chat: {
        id: number;
        type: 'self' | 'private' | 'group';
        header: {
            title: string;
            avatar: string | null;
            link: {
                route: string;
                params: any[];
            };
        };
        participants: Array<{
            id: number;
            username: string;
        }>;
    };
}>();

const inputRef = ref<HTMLInputElement | null>(null);
const message = ref('');
const messages = ref<any[]>([]);
const hasMoreMessages = ref(true);
const isLoadingMore = ref(false);

const page = usePage();
const myUserId = page.props.auth.user.id;
const messagesListRef = ref<InstanceType<typeof MessagesList>>(null)


const messagesWithMeta = computed(() => {
    if (!Array.isArray(messages.value)) return [];

    return messages.value.map((msg) => ({
        ...msg,
        isFromMe: msg.user?.id === myUserId,
    }));
});

onMounted(() => {
    fetchMessages();
    sidebarState.activeChatId = props.chat.id

});

onUnmounted(() => {
    sidebarState.activeChatId = 0
})

watch(() => replyState.message, (newMessage) => {
    if (newMessage) {
        inputRef.value?.focus();
    }
});

useEcho(`chat.${props.chat.id}`, '.message.sent', (e: any) => {
    if (!e.message) return

    const isFromMe = e.message.user.id === myUserId
    if(e.message.user.id === myUserId) return;

    const messageExists = messages.value.some((msg) => msg.id === e.message.id);
    if (!messageExists) {
        messages.value.push({
            ...e.message,
            isFromMe: isFromMe,
        });
    }
});

useEcho(`chat.${props.chat.id}`, '.message.deleted', (e: any) => {
    if (!e.messageId) return;
    if(e.originalUserId === myUserId) return;

    messages.value = messages.value.filter((msg) => msg.id !== e.messageId);
});

const handleEmojiSelect = (emoji: string) => {
    const input = inputRef.value;
    if (!input) return;

    const start = input.selectionStart || 0;
    const end = input.selectionEnd || 0;

    message.value = message.value.substring(0, start) + emoji + message.value.substring(end);

    nextTick(() => {
        input.focus();
        input.selectionStart = input.selectionEnd = start + emoji.length;
    });
};

const send = () => {
    if (!message.value.trim()) return;

    axios
        .post(route('chat.messages.store', props.chat.id), {
            body: message.value,
            reply_for_message_id: replyState.message?.id ?? null,
        })
        .then((response) => {
            messages.value.push({
                ...response.data.data
            });
        });

    replyState.message = null;
    message.value = '';
};

const fetchMessages = async () => {
    const res = await axios.get(route('chat.messages.index', props.chat.id));
    messages.value = res.data.data;
    hasMoreMessages.value = res.data.data.length === 50;
};

const fetchAndScrollToMessage = async (messageId: number) => {
    isLoadingMore.value = true;
    const oldestMessageId = messages.value[0]?.id;
    try {
        const res = await axios.get(route('chat.messages.context', { chat: props.chat.id, message: messageId, cursor: oldestMessageId}));
        if (res.data.data.length > 0) {
            messages.value = [...res.data.data, ...messages.value];
        }
        if (res.data.data.length < 30) {
            hasMoreMessages.value = false;
        }

        await nextTick()
        messagesListRef.value?.scrollToMessage(messageId)

    } catch (error) {
        mainPopupState.show('Failed to load message context.', 'error');
    } finally {
        isLoadingMore.value = false;
    }
};

const loadMoreMessages = async () => {
    if (!hasMoreMessages.value || isLoadingMore.value) return;

    isLoadingMore.value = true;
    const oldestMessageId = messages.value[0]?.id;
    if (!oldestMessageId) {
        isLoadingMore.value = false;
        return;
    }

    try {
        const res = await axios.get(route('chat.messages.index', props.chat.id), {
            params: { cursor: oldestMessageId },
        });
        if (res.data.data.length > 0) {
            messages.value = [...res.data.data, ...messages.value];
        }
        if (res.data.data.length < 50) {
            hasMoreMessages.value = false;
        }
    } catch (error) {
        mainPopupState.show('Failed to load more messages.', 'error');
    } finally {
        isLoadingMore.value = false;
    }
};
</script>

<template>
    <Head :title="props.chat.header.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-screen flex-col">
            <ChatNavBar :chat="props.chat" />

            <MessagesList
                ref="messagesListRef"
                :messages="messagesWithMeta"
                :chatType="props.chat.type"
                :has-more-messages="hasMoreMessages"
                :is-loading-more="isLoadingMore"
                @load-more="loadMoreMessages"
                @fetch-and-scroll="fetchAndScrollToMessage"
            />

            <div class="border-t border-gray-300 bg-gray-100 p-3 dark:border-gray-700 dark:bg-gray-900">
                <ReplyPreview />
                <form @submit.prevent="send" class="flex items-center gap-3">
                    <Emoji @select="handleEmojiSelect"/>

                    <input
                        ref="inputRef"
                        v-model="message"
                        type="text"
                        placeholder="Write a message..."
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:ring-purple-950"
                    />

                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white transition hover:bg-blue-700 dark:bg-purple-800 dark:hover:bg-purple-900"
                    >
                        Send
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>

    <MessageContextMenu />
</template>
