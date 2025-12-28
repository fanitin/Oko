<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import Message from '@/components/custom/chat/Message.vue';

const props = defineProps<{
    messages: Array<any>;
    chatType: 'self' | 'private' | 'group';
}>();

const containerRef = ref<HTMLElement | null>(null);

watch(
    () => props.messages.length,
    async () => {
        await nextTick();
        containerRef.value?.scrollTo({
            top: containerRef.value.scrollHeight,
            behavior: 'smooth',
        });
    }
);
</script>

<template>
    <div
        ref="containerRef"
        class="flex-1 min-h-0 overflow-y-auto bg-gray-200 p-4 space-y-3 dark:bg-gray-950 scrollbar-thin scrollbar-thumb-rounded scrollbar-thumb-gray-400 dark:scrollbar-thumb-gray-700 scrollbar-track-transparent"
    >
        <Message
            v-for="msg in messages"
            :key="msg.id"
            :message="msg"
            :chatType="chatType"
        />
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
