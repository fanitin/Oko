<script setup lang="ts">
import DateSeparator from '@/components/custom/chat/DateSeparator.vue';
import Message from '@/components/custom/chat/Message.vue';
import { nextTick, ref, watch } from 'vue';

const props = defineProps<{
    messages: Array<any>;
    chatType: 'self' | 'private' | 'group';
}>();

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

watch(
    () => props.messages.length,
    () => scrollToBottom(),
    { immediate: true },
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
</script>

<template>
    <div
        ref="containerRef"
        class="scrollbar-thin scrollbar-thumb-rounded scrollbar-thumb-gray-400 dark:scrollbar-thumb-gray-700 scrollbar-track-transparent min-h-0 flex-1 space-y-1 overflow-y-auto bg-gray-200 p-4 dark:bg-gray-950"
    >
        <template v-for="(msg, index) in messages" :key="msg.id">
            <DateSeparator v-if="shouldShowDateSeparator(msg, messages[index - 1])" :date="msg.created_at" />
            <Message :message="msg" :chatType="chatType" />
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
