<script setup lang="ts">
import { computed, ref } from 'vue';

type PopupType = 'success' | 'error' | 'info' | 'warning';

interface Props {
    duration?: number;
}

const props = defineProps<Props>();
const isVisible = ref(false);
const popupMessage = ref('');
const type = ref<PopupType>('info');
const duration = props.duration || 3000;

function show(message: string, popupType: PopupType = 'info') {
    popupMessage.value = message;
    type.value = popupType;
    isVisible.value = true;

    setTimeout(() => {
        isVisible.value = false;
    }, duration);
}

defineExpose({ show });

const bgColor = computed(() => {
    switch (type.value) {
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
    switch (type.value) {
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
            v-if="isVisible"
            :class="[
        'fixed top-5 right-5 z-50 rounded-lg px-5 py-3 shadow-lg transition-all duration-300 transform hover:scale-105',
        bgColor,
        textColor,
      ]"
        >
            {{ popupMessage }}
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
