<script setup lang="ts">
import { FileUp } from 'lucide-vue-next'
const emit = defineEmits<{
    (e: 'files-selected', files: File[]): void
}>()

function handleChange(e: Event) {
    const input = e.target as HTMLInputElement
    if (!input.files) return

    emit('files-selected', Array.from(input.files))
    input.value = ''
}
</script>

<template>
    <label
        class="cursor-pointer rounded-lg border border-gray-300 bg-gray-100 px-2 py-1 transition hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800"
    >
        <FileUp />
        <input
            type="file"
            multiple
            class="hidden"
            @change="handleChange"
        />
    </label>
</template>
