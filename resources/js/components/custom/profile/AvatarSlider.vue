<script setup lang="ts">
import { computed, defineProps, onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface Avatar {
    id: number;
    path: string;
    is_main: boolean;
}

const props = defineProps<{ avatars?: Avatar[] }>();

const hoverIndex = ref<number | null>(null);
const currentIndex = ref(0);
const visibleAvatars = computed(() => props.avatars || []);
const total = computed(() => visibleAvatars.value.length);
const slideX = ref(0);
watch(currentIndex, (newIndex) => (slideX.value = -newIndex * 100));

function prev() {
    if (total.value) currentIndex.value = (currentIndex.value - 1 + total.value) % total.value;
}

function next() {
    if (total.value) currentIndex.value = (currentIndex.value + 1) % total.value;
}

function setMain(avatar: Avatar) {
    console.log('Set main', avatar);
}

function deleteAvatar(avatar: Avatar) {
    console.log('Delete', avatar);
}

const showMenu = ref<number | null>(null);

function toggleMenu(index: number) {
    showMenu.value = showMenu.value === index ? null : index;
}

function handleClickOutside(event: MouseEvent) {
    const menu = document.getElementById('avatar-menu');
    if (menu && !menu.contains(event.target as Node)) showMenu.value = null;
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div v-if="total" class="relative flex w-full items-center justify-center">
        <div class="relative w-5/6 overflow-hidden rounded-md">
            <div class="flex transition-transform duration-500 ease-in-out" :style="{ transform: `translateX(${slideX}%)` }">
                <div v-for="(avatar, index) in visibleAvatars" :key="avatar.id" class="relative aspect-square w-full flex-shrink-0">
                    <img :src="avatar.path" alt="avatar" class="h-full w-full rounded-md object-cover" />

                    <button
                        class="absolute top-0 right-0 px-5 py-3 text-3xl text-white opacity-50 transition hover:opacity-100"
                        @click.stop="toggleMenu(index)"
                    >
                        ...
                    </button>

                    <transition name="fade">
                        <div
                            v-if="showMenu === index"
                            id="avatar-menu"
                            class="absolute top-12 right-3 z-50 w-44 space-y-2 rounded-md bg-white p-3 shadow-lg dark:bg-gray-800"
                        >
                            <button @click="setMain(avatar)" class="w-full rounded px-2 py-1 text-left hover:bg-gray-200 dark:hover:bg-gray-700">
                                Set as main
                            </button>
                            <button @click="deleteAvatar(avatar)" class="w-full rounded px-2 py-1 text-left hover:bg-gray-200 dark:hover:bg-gray-700">
                                Delete
                            </button>
                        </div>
                    </transition>
                </div>
            </div>

            <button
                @click="prev"
                class="absolute top-1/2 left-0 z-10 -translate-y-1/2 px-6 py-12 text-5xl font-bold text-white opacity-50 transition hover:opacity-100"
            >
                ‹
            </button>
            <button
                @click="next"
                class="absolute top-1/2 right-0 z-10 -translate-y-1/2 px-6 py-12 text-5xl font-bold text-white opacity-50 transition hover:opacity-100"
            >
                ›
            </button>

            <div class="absolute bottom-0 left-0 flex w-full justify-center space-x-1">
                <span
                    v-for="(_, idx) in total"
                    :key="idx"
                    @mouseenter="hoverIndex = idx"
                    @mouseleave="hoverIndex = null"
                    @click="currentIndex = idx"
                    :class="[
                        'h-1 flex-1 cursor-pointer rounded transition-all',
                        idx === currentIndex
                            ? 'bg-white dark:bg-purple-400'
                            : hoverIndex === idx
                              ? 'bg-white/70 dark:bg-purple-500/70'
                              : 'bg-white/30 dark:bg-purple-700/50',
                    ]"
                ></span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fade-enter-to,
.fade-leave-from {
    opacity: 1;
}
</style>
