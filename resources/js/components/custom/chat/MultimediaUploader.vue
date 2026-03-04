<script setup lang="ts">
import { FileUp } from 'lucide-vue-next'
import { fileState } from '@/lib/custom/states/fileState'
import { computed } from 'vue'

const emit = defineEmits<{
    (e: 'files-selected', files: File[]): void
}>()

const filesCount = computed(() => fileState.files.length)
const canAddMore = computed(() => filesCount.value < 10)

function handleChange(e: Event) {
    const input = e.target as HTMLInputElement
    if (!input.files) return

    emit('files-selected', Array.from(input.files))
    input.value = ''
}
</script>

<template>
    <label
        class="relative cursor-pointer rounded-lg border border-gray-300 bg-gray-100 px-2 py-1 transition hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800"
        :class="{ 'opacity-50 cursor-not-allowed': !canAddMore }"
    >
        <FileUp />
        <span v-if="filesCount > 0" class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-blue-500 dark:bg-purple-600 text-white text-xs font-bold">
            {{ filesCount }}
        </span>
        <input
            type="file"
            multiple
            :disabled="!canAddMore"
            accept="image/*,video/*,.pdf,.doc,.docx,.txt,.zip,.rar"
            class="hidden"
            @change="handleChange"
        />
    </label>
</template>
