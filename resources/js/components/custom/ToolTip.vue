<script setup lang="ts">
import { ref } from 'vue';

defineProps<{
    text: string
}>();

const show = ref(false);
</script>

<template>
    <div
        class="relative inline-block"
        @mouseenter="show = true"
        @mouseleave="show = false"
    >
        <slot />
        <transition name="fade">
            <div
                v-if="show"
                class="absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap
               px-2 py-1 rounded text-sm shadow-md z-10
               bg-gray-200 text-gray-800
               dark:bg-gray-700 dark:text-gray-100"
            >
                {{ text }}
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
