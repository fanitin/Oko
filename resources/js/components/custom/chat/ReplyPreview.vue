<script setup lang="ts">
import { replyState } from '@/lib/custom/states/replyState'
import { X } from 'lucide-vue-next'

function cancelReply() {
    replyState.message = null
}
</script>

<template>
    <div
        v-if="replyState.message"
        class="mb-2 relative rounded-t-lg border-b border-gray-300 bg-gray-200/70 p-3 dark:border-gray-700 dark:bg-gray-800/70"
    >
        <div class="rounded-md border-l-4 border-blue-400 bg-blue-50/50 py-1.5 pr-8 pl-2 text-sm dark:border-purple-500 dark:bg-purple-900/20">
            <div class="font-semibold text-blue-700 dark:text-purple-300">{{ replyState.message.user.username }}</div>
            <div class="max-w-full truncate text-gray-900 dark:text-gray-300">
                {{ replyState.message.body }}
            </div>
        </div>
        <button
            @click="cancelReply"
            class="absolute top-1/2 right-3 -translate-y-1/2 rounded-full p-1 text-gray-500 hover:bg-gray-300 dark:text-gray-400 dark:hover:bg-gray-600"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>
