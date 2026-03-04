<script setup lang="ts">
import { editState } from '@/lib/custom/states/editState'
import { clearFiles } from '@/lib/custom/states/fileState'
import { Pencil, X } from 'lucide-vue-next'

function cancelEdit() {
    editState.message = null
    clearFiles()
}
</script>

<template>
    <div
        v-if="editState.message"
        class="mb-2 relative flex items-center gap-3 rounded-t-lg border-b-2 border-gray-300 bg-gray-100 p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800/70"
    >
        <Pencil class="h-5 w-5 text-blue-500 dark:text-purple-400" />

        <div class="flex-1 min-w-0 rounded-md border-l-4 border-blue-400 bg-blue-50/50 py-1.5 pr-8 pl-2 text-sm dark:border-purple-500 dark:bg-purple-900/20">
            <div class="font-semibold text-blue-700 dark:text-purple-300">{{ editState.message.user.username }}</div>
            <div class="truncate text-gray-900 dark:text-gray-300">
                {{ editState.message.body }}
            </div>
        </div>
        <button
            @click="cancelEdit"
            class="absolute top-1/2 right-3 -translate-y-1/2 rounded-full p-1 text-gray-500 hover:bg-gray-300 dark:text-gray-400 dark:hover:bg-gray-600"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>
