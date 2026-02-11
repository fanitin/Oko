<script setup lang="ts">
import { onMounted, onBeforeUnmount } from 'vue'
import MainButtonComponent from '@/components/custom/MainButtonComponent.vue';

const props = defineProps<{
    modelValue: boolean
    title?: string
    description?: string
    confirmText?: string
    cancelText?: string
    variant?: 'primary' | 'secondary' | 'danger' | 'success'
    loading?: boolean
}>()

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

function close() {
    emit('update:modelValue', false)
    emit('cancel')
}

function confirm() {
    emit('confirm')
}

function handleEsc(e: KeyboardEvent) {
    if (e.key === 'Escape' && props.modelValue) {
        close()
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleEsc)
})

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleEsc)
})
</script>

<template>
    <transition name="fade">
        <div
            v-if="modelValue"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            @click.self="close"
        >
            <div
                class="w-full max-w-md rounded-2xl bg-white dark:bg-zinc-900 p-6 shadow-2xl border border-zinc-200 dark:border-zinc-800"
            >
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">
                    {{ title }}
                </h2>

                <p
                    v-if="description"
                    class="text-sm text-zinc-600 dark:text-zinc-400 mb-6"
                >
                    {{ description }}
                </p>

                <div class="flex justify-end gap-3">
                    <MainButtonComponent
                        type="secondary"
                        @click="close"
                    >
                        {{ cancelText ?? 'Cancel' }}
                    </MainButtonComponent>

                    <MainButtonComponent
                        :type="variant ?? 'primary'"
                        :disabled="loading"
                        @click="confirm"
                    >
                        <span v-if="!loading">
                            {{ confirmText ?? 'Confirm' }}
                        </span>
                        <span v-else class="animate-pulse">
                            Loading...
                        </span>
                    </MainButtonComponent>
                </div>
            </div>
        </div>
    </transition>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
