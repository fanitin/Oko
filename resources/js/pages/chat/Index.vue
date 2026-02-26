<script setup lang="ts">
import ChatContextMenu from '@/components/custom/chat/ChatContextMenu.vue';
import ChatNavBar from '@/components/custom/chat/ChatNavBar.vue';
import EditPreview from '@/components/custom/chat/EditPreview.vue';
import Emoji from '@/components/custom/chat/Emoji.vue';
import MessageContextMenu from '@/components/custom/chat/MessageContextMenu.vue';
import MessagesList from '@/components/custom/chat/MessagesList.vue';
import ReplyPreview from '@/components/custom/chat/ReplyPreview.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { editState } from '@/lib/custom/states/editState';
import { mainPopupState } from '@/lib/custom/states/mainPopupState';
import { replyState } from '@/lib/custom/states/replyState';
import { sidebarState } from '@/lib/custom/states/sidebarState';
import { fileState, addFiles, clearFiles } from '@/lib/custom/states/fileState';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import { debounce } from 'lodash-es';
import { ArrowDown, MessageCircle, Sparkles } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import MultimediaUploader from "@/components/custom/chat/MultimediaUploader.vue";
import MediaPreviewBar from '@/components/custom/chat/MediaPreviewBar.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chat',
        href: '/chat',
    },
];

const props = defineProps<{
    chat: {
        id: number;
        type: 'self' | 'private' | 'group';
        settings: {
            isPinned: boolean;
            isMuted: boolean;
        };
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
const messagesListRef = ref<InstanceType<typeof MessagesList> | null>(null);

const isUserAtBottom = ref(true);
const newMessagesCount = ref(0);
const pendingMessages = ref(false);

const isEditMode = computed(() => !!editState.message);

const messagesWithMeta = computed(() => {
    if (!Array.isArray(messages.value)) return [];

    return messages.value.map((msg) => ({
        ...msg,
        isFromMe: msg.user?.id === myUserId,
    }));
});

const onFocus = () => {
    if (sidebarState.activeChatId === props.chat.id) {
        markAsRead();
    }
};

onMounted(() => {
    window.addEventListener('focus', onFocus);
    fetchMessages();
    sidebarState.activeChatId = props.chat.id;
    markAsRead();
});

onBeforeUnmount(() => {
    window.removeEventListener('focus', onFocus);
    sidebarState.activeChatId = 0;
});

watch(
    () => replyState.message,
    (newMessage) => {
        if (newMessage) {
            inputRef.value?.focus();
        }
    },
);

watch(
    () => editState.message,
    (newMessage) => {
        if (newMessage) {
            message.value = newMessage.body;
            inputRef.value?.focus();
        } else {
            message.value = '';
        }
    },
);

watch(isEditMode, (val) => {
    if (val && fileState.files.length > 0) {
        clearFiles();
    }
});

const handleNewMessage = (msg: any, isFromMe: boolean) => {
    messages.value.push({
        ...msg,
        isFromMe: isFromMe,
    });

    if (!isUserAtBottom.value) {
        newMessagesCount.value++;
    }
};

useEcho(`chat.${props.chat.id}`, '.message.sent', (e: any) => {
    if (!e.message) return;
    const isFromMe = e.message.user.id === myUserId;
    if (isFromMe) return;

    const messageExists = messages.value.some((msg) => msg.id === e.message.id);
    if (!messageExists) {
        handleNewMessage(e.message, isFromMe);

        markAsReadDebounced();
    }
});

useEcho(`chat.${props.chat.id}`, '.message.deleted', (e: any) => {
    if (!e.messageId) return;

    messages.value = messages.value.filter((msg) => msg.id !== e.messageId);
});

useEcho(`chat.${props.chat.id}`, '.message.markAsRead', (e: any) => {
    if (!e.chatId) return;
    if (!e.userId) return;

    messages.value.map((msg) => {
        if (msg.user.id != e.userId) {
            msg.status = 'seen';
        }
    });
});

useEcho(`chat.${props.chat.id}`, '.message.edited', (e: any) => {
    if (!e.message) return;
    if (e.message.chat_id != props.chat.id) return;

    const index = messages.value.findIndex((msg) => msg.id === e.message.id);
    if (index !== -1) {
        messages.value[index] = {
            ...e.message,
            isFromMe: e.message.user.id === myUserId,
        };
    }
});

useEcho(`chat.${props.chat.id}`, '.chat.deleted', (e: any) => {
    if (!e.chatID) return;
    if (e.chatID != props.chat.id) return;

    sidebarState.removeChat(e.chatID);
    router.visit(route('home'));
    mainPopupState.show('This chat has been deleted.', 'info');
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

function handleFilesSelected(files: File[]) {
    addFiles(files);
}

const send = async () => {
    if (!message.value.trim() && fileState.files.length === 0) return;

    const formData = new FormData();
    formData.append('body', message.value);
    formData.append('reply_for_message_id', replyState.message?.id ? String(replyState.message.id) : '');

    if (!isEditMode.value) {
        fileState.files.forEach((f, idx) => {
            formData.append('media[' + idx + ']', f.file);
        });
    }

    if (editState.message) {
        await axios.post(route('chat.messages.update', { chat: props.chat.id, message: editState.message.id }), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        }).then((response) => {
            const index = messages.value.findIndex((msg) => msg.id === response.data.data.id);
            if (index !== -1) {
                messages.value[index] = {
                    ...response.data.data,
                    isFromMe: true,
                };
            }
        });
        editState.message = null;
        message.value = '';
        clearFiles();
        return;
    }
    await axios.post(route('chat.messages.store', props.chat.id), formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    }).then((response) => {
        handleNewMessage(response.data.data, true);
    });
    replyState.message = null;
    message.value = '';
    clearFiles();
};

const fetchMessages = async () => {
    pendingMessages.value = true;
    const res = await axios.get(route('chat.messages.index', props.chat.id));
    messages.value = res.data.data;
    hasMoreMessages.value = res.data.data.length === 50;
    pendingMessages.value = false;
};

const fetchAndScrollToMessage = async (messageId: number) => {
    isLoadingMore.value = true;
    const oldestMessageId = messages.value[0]?.id;
    try {
        const res = await axios.get(
            route('chat.messages.context', {
                chat: props.chat.id,
                message: messageId,
                cursor: oldestMessageId,
            }),
        );
        if (res.data.data.length > 0) {
            messages.value = [...res.data.data, ...messages.value];
        }
        if (res.data.data.length < 30) {
            hasMoreMessages.value = false;
        }

        await nextTick();
        messagesListRef.value?.scrollToMessage(messageId);
    } catch {
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
    } catch {
        mainPopupState.show('Failed to load more messages.', 'error');
    } finally {
        isLoadingMore.value = false;
    }
};

const markAsRead = () => {
    axios.post(route('chat.messages.mark-as-read', props.chat.id)).then(() => {
        sidebarState.resetUnreadCount(props.chat.id);
    });
};

const markAsReadDebounced = debounce(() => {
    if (sidebarState.activeChatId !== props.chat.id) return;
    if (!document.hasFocus()) return;

    markAsRead();
}, 3000);

const onScrollStatusChange = (isAtBottom: boolean) => {
    isUserAtBottom.value = isAtBottom;
    if (isAtBottom) {
        newMessagesCount.value = 0;
    }
};

const scrollToBottom = () => {
    messagesListRef.value?.scrollToBottomSmooth();
    newMessagesCount.value = 0;
};
</script>

<template>
    <Head :title="props.chat.header.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-screen flex-col">
            <ChatNavBar :chat="props.chat" />
            <div class="relative flex min-h-0 flex-1 flex-col">
                <MessagesList
                    v-if="messages.length > 0 || pendingMessages"
                    ref="messagesListRef"
                    :messages="messagesWithMeta"
                    :chatType="props.chat.type"
                    :has-more-messages="hasMoreMessages"
                    :is-loading-more="isLoadingMore"
                    @load-more="loadMoreMessages"
                    @fetch-and-scroll="fetchAndScrollToMessage"
                    @scroll-status="onScrollStatusChange"
                />

                <div v-else class="flex flex-1 items-center justify-center p-4">
                    <div
                        class="w-full max-w-md rounded-xl border-2 border-gray-300 bg-white p-8 text-center shadow-md dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="mb-4 flex justify-center">
                            <div class="rounded-full bg-blue-100 p-4 dark:bg-purple-900/30">
                                <MessageCircle class="h-12 w-12 text-blue-600 dark:text-purple-500" />
                            </div>
                        </div>

                        <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-gray-100">No messages yet</h3>

                        <p class="mb-6 text-gray-600 dark:text-gray-400">Start a conversation by sending a message below</p>

                        <div class="flex items-center justify-center gap-2 text-sm text-gray-500 dark:text-gray-500">
                            <Sparkles class="h-4 w-4" />
                            <span>Be the first to say hello!</span>
                        </div>
                    </div>
                </div>

                <transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-2"
                >
                    <button
                        v-if="!isUserAtBottom"
                        @click="scrollToBottom"
                        class="absolute right-4 bottom-4 z-10 flex h-10 w-10 items-center justify-center rounded-full border-2 border-gray-300 bg-white shadow-lg transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
                    >
                        <ArrowDown class="h-5 w-5 text-gray-600 dark:text-gray-300" />

                        <span
                            v-if="newMessagesCount > 0"
                            class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-green-500 text-xs font-bold text-white shadow-sm dark:bg-purple-700"
                        >
                            {{ newMessagesCount > 99 ? '99+' : newMessagesCount }}
                        </span>
                    </button>
                </transition>
            </div>

            <div class="border-t-2 border-gray-300 bg-gray-50 p-3 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <ReplyPreview />
                <EditPreview />
                <MediaPreviewBar />
                <form @submit.prevent="send" class="flex items-center gap-3">
                    <Emoji @select="handleEmojiSelect" />
                    <MultimediaUploader @files-selected="handleFilesSelected" v-if="!isEditMode" />

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
    <ChatContextMenu />
</template>
