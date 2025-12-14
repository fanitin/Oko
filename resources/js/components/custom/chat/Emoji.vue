<script setup lang="ts">
import { ref } from 'vue';

const emit = defineEmits<{
    (e: 'select', emoji: string): void;
}>();

const show = ref(false);
const pickerRef = ref<HTMLDivElement | null>(null);

const emojiList = [
    '😀','😁','😂','🤣','😃','😄','😅','😆','😉','😊',
    '😋','😎','😍','😘','🥰','😗','😙','😚','🙂','🤗',
    '🤩','🤔','🤨','😐','😑','😶','🙄','😏','😣','😥',
    '😮','🤐','😯','😪','😫','🥱','😴','😌','😛','😜',
    '😝','🤤','😒','😓','😔','😕','🙃','🤑','😲','☹️',
    '🥳','🤯','😳','🥺','😡','😠','🤬','😷','🤒','🤕'
];

function toggle() {
    show.value = !show.value;
}

function selectEmoji(emoji: string) {
    emit('select', emoji);
    show.value = false;
}
</script>

<template>
    <div class="relative">
        <button
            type="button"
            @click="toggle"
            class="px-2 py-1 bg-gray-200 dark:bg-gray-900 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-800 transition"
        >
            😝
        </button>

        <transition name="fade">
            <div
                v-if="show"
                ref="pickerRef"
                class="emoji-scroll absolute bottom-full left-0 mb-2 w-64 max-h-60 overflow-y-auto
               bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-700
               rounded-lg shadow-lg p-2 flex flex-wrap gap-1 z-50"
            >
        <span
            v-for="(emoji, index) in emojiList"
            :key="index"
            class="cursor-pointer text-2xl hover:scale-125 transition"
            @click="selectEmoji(emoji)"
        >
          {{ emoji }}
        </span>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
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
    scrollbar-color: rgba(120,120,120,.4) transparent;
}
</style>
