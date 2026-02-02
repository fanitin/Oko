<script setup lang="ts">
import { Smile } from 'lucide-vue-next';
import { ref } from 'vue';

const emit = defineEmits<{
    (e: 'select', emoji: string): void;
}>();

const show = ref(false);
const pickerRef = ref<HTMLDivElement | null>(null);

const emojiList = [
    '😀',
    '😁',
    '😂',
    '🤣',
    '😃',
    '😄',
    '😅',
    '😆',
    '😉',
    '😊',
    '😋',
    '😎',
    '😍',
    '😘',
    '🥰',
    '😗',
    '😙',
    '😚',
    '🙂',
    '🤗',
    '🤩',
    '🤔',
    '🤨',
    '😐',
    '😑',
    '😶',
    '🙄',
    '😏',
    '😣',
    '😥',
    '😮',
    '🤐',
    '😯',
    '😪',
    '😫',
    '🥱',
    '😴',
    '😌',
    '😛',
    '😜',
    '😝',
    '🤤',
    '😒',
    '😓',
    '😔',
    '😕',
    '🙃',
    '🤑',
    '😲',
    '☹️',
    '🥳',
    '🤯',
    '😳',
    '🥺',
    '😡',
    '😠',
    '🤬',
    '😷',
    '🤒',
    '🤕',
];

function toggle() {
    show.value = !show.value;
}

function close() {
    show.value = false;
}

function selectEmoji(emoji: string) {
    emit('select', emoji);
    // show.value = false;
}
</script>

<template>
    <div class="relative">
        <button
            type="button"
            @click="toggle"
            class="rounded-lg bg-gray-200 px-2 py-1 transition hover:bg-gray-300 dark:bg-gray-900 dark:hover:bg-gray-800"
        >
            <Smile />
        </button>

        <transition name="fade">
            <div
                v-if="show"
                ref="pickerRef"
                class="emoji-scroll absolute bottom-full left-0 z-50 mb-2 flex max-h-60 w-64 flex-wrap gap-1 overflow-y-auto rounded-lg border border-gray-300 bg-gray-100 p-2 shadow-lg dark:border-gray-700 dark:bg-gray-900"
                v-click-outside="{ handler: close, ignore: buttonRef }"
            >
                <span
                    v-for="(emoji, index) in emojiList"
                    :key="index"
                    class="cursor-pointer text-2xl transition hover:scale-125"
                    @click="selectEmoji(emoji)"
                >
                    {{ emoji }}
                </span>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.emoji-scroll::-webkit-scrollbar {
    width: 6px;
}

.emoji-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.emoji-scroll::-webkit-scrollbar-thumb {
    background-color: rgba(120, 120, 120, 0.4);
    border-radius: 999px;
}

.dark .emoji-scroll::-webkit-scrollbar-thumb {
    background-color: rgba(180, 180, 180, 0.35);
}

.emoji-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(120, 120, 120, 0.4) transparent;
}
</style>
