<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    date: string;
}>();

const formattedDate = computed(() => {
    const messageDate = new Date(props.date);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (messageDate.toDateString() === today.toDateString()) {
        return 'Today';
    }
    if (messageDate.toDateString() === yesterday.toDateString()) {
        return 'Yesterday';
    }
    return messageDate.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
});
</script>

<template>
    <div class="flex items-center justify-center py-2">
        <span class="rounded-full bg-gray-200 border border-gray-300 px-3 py-1 text-xs font-semibold text-gray-700 shadow-sm dark:bg-gray-800/70 dark:border-gray-700 dark:text-gray-400">
            {{ formattedDate }}
        </span>
    </div>
</template>
