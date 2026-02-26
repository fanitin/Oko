<script setup lang="ts">
import { X, FileText } from 'lucide-vue-next'
import { fileState, removeFile } from '@/lib/custom/states/fileState'
</script>

<template>
    <div
        v-if="fileState.files.length"
        class="mb-3 flex gap-3 overflow-x-auto rounded-xl border bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900 shadow-md"
    >
        <div
            v-for="(f, i) in fileState.files"
            :key="i"
            class="relative h-24 w-32 flex-shrink-0 overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-center"
        >
            <img
                v-if="f.type === 'image'"
                :src="f.preview"
                class="h-full w-full object-cover rounded-lg"
            />

            <video
                v-else-if="f.type === 'video'"
                :src="f.preview"
                muted
                class="h-full w-full object-cover rounded-lg"
            />

            <div
                v-else
                class="flex flex-col items-center justify-center w-full h-full p-2 bg-gray-100 dark:bg-gray-700 rounded-lg"
            >
                <FileText class="w-8 h-8 text-gray-400 mb-2" />
                <span class="text-xs text-gray-600 dark:text-gray-300 truncate w-full text-center">{{ f.file.name }}</span>
            </div>

            <button
                @click="removeFile(i)"
                class="absolute top-1 right-1 flex h-7 w-7 items-center justify-center rounded-full bg-black/70 text-white hover:bg-black transition shadow-lg"
            >
                <X :size="16" />
            </button>
        </div>
    </div>
</template>
