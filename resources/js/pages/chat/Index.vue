<script setup lang="ts">
import ChatNavBar from '@/components/custom/chat/ChatNavBar.vue';
import Emoji from '@/components/custom/chat/Emoji.vue';
import MessageContextMenu from '@/components/custom/chat/MessageContextMenu.vue';
import MessagesList from '@/components/custom/chat/MessagesList.vue';
import ReplyPreview from '@/components/custom/chat/ReplyPreview.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { mainPopupState } from '@/lib/custom/states/mainPopupState';
import { replyState } from '@/lib/custom/states/replyState';
import { sidebarState } from '@/lib/custom/states/sidebarState';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import axios from 'axios';
import { computed, nextTick, onMounted, onBeforeUnmount, ref, watch } from 'vue';
import { debounce } from 'lodash-es'
import { ArrowDown } from 'lucide-vue-next';
import EditPreview from '@/components/custom/chat/EditPreview.vue';
import { editState } from '@/lib/custom/states/editState';

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

const messagesWithMeta = computed(() => {
    if (!Array.isArray(messages.value)) return [];

    return messages.value.map((msg) => ({
        ...msg,
        isFromMe: msg.user?.id === myUserId,
    }));
});

const onFocus = () => {
    if (sidebarState.activeChatId === props.chat.id) {
        markAsRead()
    }
}

onMounted(() => {
    window.addEventListener('focus', onFocus)
    fetchMessages();
    sidebarState.activeChatId = props.chat.id;
    markAsRead();
});

onBeforeUnmount(() => {
    window.removeEventListener('focus', onFocus)
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
    if (e.message.user.id === myUserId) return;

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
    if(!e.userId) return;

    messages.value.map((msg) => {
        if(msg.user.id != e.userId) {
            msg.status = 'seen';
        }
    })
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

    if(editState.message) {
        axios
            .put(route('chat.messages.update', {chat: props.chat.id, message: editState.message.id}), {
                body: message.value,
            })
            .then((response) => {
                const index = messages.value.findIndex(msg => msg.id === response.data.data.id);
                if(index !== -1) {
                    messages.value[index] = {
                        ...response.data.data,
                        isFromMe: true,
                    };
                }
            });
        //TODO BACKEND FOR THIS + TESTS
        editState.message = null;
        message.value = '';
        return;
    }
    axios
        .post(route('chat.messages.store', props.chat.id), {
            body: message.value,
            reply_for_message_id: replyState.message?.id ?? null,
        })
        .then((response) => {
            handleNewMessage(response.data.data, true);
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
    } catch (e) {
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
    } catch (e) {
        mainPopupState.show('Failed to load more messages.', 'error');
    } finally {
        isLoadingMore.value = false;
    }
};

const markAsRead = () => {
    axios.post(route('chat.messages.mark-as-read', props.chat.id))
        .then(() => {
            sidebarState.resetUnreadCount(props.chat.id);
        });
};

const markAsReadDebounced = debounce(() => {
    if (sidebarState.activeChatId !== props.chat.id) return
    if (!document.hasFocus()) return

    markAsRead()
}, 3000)

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

            <div class="relative flex-1 min-h-0 flex flex-col">
                <MessagesList
                    ref="messagesListRef"
                    :messages="messagesWithMeta"
                    :chatType="props.chat.type"
                    :has-more-messages="hasMoreMessages"
                    :is-loading-more="isLoadingMore"
                    @load-more="loadMoreMessages"
                    @fetch-and-scroll="fetchAndScrollToMessage"
                    @scroll-status="onScrollStatusChange"
                />

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
                        class="absolute bottom-4 right-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-md transition hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700"
                    >
                        <ArrowDown class="h-5 w-5 text-gray-600 dark:text-gray-300" />

                        <span
                            v-if="newMessagesCount > 0"
                            class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-green-500 dark:bg-purple-700 text-xs font-bold text-white shadow-sm"
                        >
                            {{ newMessagesCount > 99 ? '99+' : newMessagesCount }}
                        </span>
                    </button>
                </transition>
            </div>

            <div class="border-t border-gray-300 bg-gray-100 p-3 dark:border-gray-700 dark:bg-gray-900">
                <ReplyPreview />
                <EditPreview />
                <form @submit.prevent="send" class="flex items-center gap-3">
                    <Emoji @select="handleEmojiSelect" />

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
