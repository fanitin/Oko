<script setup lang="ts">
import { defineProps, defineEmits } from 'vue'

interface Props {
    type?: 'primary' | 'secondary' | 'danger' | 'success'
    class?: string
    disabled?: boolean
}

const props = defineProps<Props>()
const emit = defineEmits<{
    (e: 'click', event: MouseEvent): void
}>()

const baseClasses = {
    primary: 'bg-emerald-500 text-white hover:bg-emerald-600 dark:bg-purple-600 dark:hover:bg-purple-700',
    secondary: 'bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600',
    danger: 'bg-red-500 text-white hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700',
    success: 'bg-green-500 text-white hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700',
}
const typeClass = baseClasses[props.type || 'primary']
</script>

<template>
    <button
        :class="`${typeClass} px-4 py-2 rounded ${props.class || ''}`"
        :disabled="props.disabled"
        @click="$emit('click', $event)"
    >
        <slot />
    </button>
</template>
