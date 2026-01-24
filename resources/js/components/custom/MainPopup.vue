<script setup lang="ts">
import { computed } from 'vue';
import { mainPopupState } from '@/lib/custom/states/mainPopupState';

const bgColor = computed(() => {
    switch (mainPopupState.type) {
        case 'success':
            return 'bg-emerald-500 dark:bg-purple-600';
        case 'error':
            return 'bg-red-500 dark:bg-red-700';
        case 'warning':
            return 'bg-yellow-400 dark:bg-yellow-600';
        case 'info':
        default:
            return 'bg-blue-500 dark:bg-blue-700';
    }
});

const textColor = computed(() => {
    switch (mainPopupState.type) {
        case 'warning':
            return 'text-gray-900 dark:text-gray-100';
        default:
            return 'text-white';
    }
});
</script>

<template>
    <transition name="fade">
        <div
            v-if="mainPopupState.isVisible"
            :class="[
        'fixed top-5 right-5 z-50 rounded-lg px-5 py-3 shadow-lg transition-all duration-300 transform hover:scale-105',
        bgColor,
        textColor,
      ]"
        >
            {{ mainPopupState.message }}
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
