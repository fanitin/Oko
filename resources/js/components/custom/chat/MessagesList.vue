<script setup lang="ts">
import DateSeparator from '@/components/custom/chat/DateSeparator.vue';
import Message from '@/components/custom/chat/Message.vue';
import { LoaderCircle } from 'lucide-vue-next';
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{
    messages: Array<any>;
    chatType: 'self' | 'private' | 'group';
    hasMoreMessages: boolean;
    isLoadingMore: boolean;
}>();

const emit = defineEmits(['load-more', 'fetch-and-scroll', 'scroll-status']);

const containerRef = ref<HTMLElement | null>(null);

const scrollToBottom = async (behavior: 'smooth' | 'auto' = 'smooth') => {
    await nextTick();
    if (containerRef.value) {
        containerRef.value.scrollTo({
            top: containerRef.value.scrollHeight,
            behavior,
        });
    }
};

const handleScroll = () => {
    const el = containerRef.value;
    if (!el) return;

    if (el.scrollTop === 0 && props.hasMoreMessages && !props.isLoadingMore) {
        emit('load-more');
    }

    const isAtBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 100;
    emit('scroll-status', isAtBottom);
};

onMounted(() => {
    containerRef.value?.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    containerRef.value?.removeEventListener('scroll', handleScroll);
});

watch(
    () => props.messages,
    async (newMessages, oldMessages) => {
        const newLength = newMessages.length;
        const oldLength = oldMessages?.length ?? 0;

        if (newLength === 0) return;

        const el = containerRef.value;
        if (!el) return;

        const isInitialLoad = oldLength === 0;
        if (isInitialLoad) {
            await scrollToBottom('auto');
            return;
        }

        const isAddingNewMessage = newLength > oldLength && newMessages[newLength - 1].id > (oldMessages[oldLength - 1]?.id ?? 0);
        if (isAddingNewMessage) {
            if (el.scrollHeight - el.scrollTop - el.clientHeight < 200) {
                await scrollToBottom('smooth');
            }
            return;
        }

        const isPrependingOldMessages = newLength > oldLength;
        if (isPrependingOldMessages) {
            const oldScrollHeight = el.scrollHeight;
            await nextTick();
            el.scrollTop = el.scrollHeight - oldScrollHeight;
        }
    },
    { deep: true },
);

const isSameDay = (d1: Date, d2: Date) => {
    return d1.getFullYear() === d2.getFullYear() && d1.getMonth() === d2.getMonth() && d1.getDate() === d2.getDate();
};

const shouldShowDateSeparator = (currentMessage: any, previousMessage: any) => {
    if (!previousMessage) return true;
    const currentDate = new Date(currentMessage.created_at);
    const previousDate = new Date(previousMessage.created_at);
    return !isSameDay(currentDate, previousDate);
};

const handleFetchAndScroll = (messageId: number) => {
    emit('fetch-and-scroll', messageId);
};

const messageRefs = ref<Record<number, InstanceType<typeof Message>>>({});

const setMessageRef = (id: number, instance: any) => {
    messageRefs.value[id] = instance;
};

const scrollToMessage = async (messageId: number) => {
    await nextTick();
    const msgComp = messageRefs.value[messageId];
    if (msgComp) {
        msgComp.scrollToReply(messageId);
    }
};

const scrollToBottomSmooth = () => scrollToBottom('smooth');


defineExpose({
    scrollToMessage,
    setMessageRef,
    scrollToBottomSmooth,
});
</script>

<template>
    <div
        ref="containerRef"
        class="scrollbar-thin scrollbar-thumb-rounded scrollbar-thumb-gray-400 dark:scrollbar-thumb-gray-700 scrollbar-track-transparent min-h-0 flex-1 space-y-1 overflow-y-auto bg-gray-200 p-4 dark:bg-gray-950"
    >
        <div v-if="isLoadingMore" class="flex justify-center py-2">
            <LoaderCircle class="h-6 w-6 animate-spin text-gray-500" />
        </div>

        <template v-for="(msg, index) in messages" :key="msg.id">
            <DateSeparator v-if="shouldShowDateSeparator(msg, messages[index - 1])" :date="msg.created_at" />
            <Message :message="msg"
                     :chatType="chatType"
                     @fetch-and-scroll="handleFetchAndScroll"
                     :ref="(el) => setMessageRef(msg.id, el)"
            />
        </template>
    </div>
</template>
<style scoped>
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background-color: rgba(100, 100, 100, 0.4);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background-color: rgba(100, 100, 100, 0.6);
}
</style>
