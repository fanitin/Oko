<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { MoreVertical } from 'lucide-vue-next';
import { computed, defineProps, onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface Avatar {
    id: number;
    path: string;
    is_main: boolean;
}

const emit = defineEmits<{
    (e: 'avatarSetAsMain', payload: { success: boolean; message: string }): void;
}>();
const props = defineProps<{ avatars?: Avatar[] }>();

const hoverIndex = ref<number | null>(null);
const currentIndex = ref(0);
const visibleAvatars = ref<Avatar[]>(props.avatars ?? []);
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
    axios
        .post('/profile/settings/set-as-main', avatar)
        .then((res) => {
            visibleAvatars.value = res.data.avatars;
            currentIndex.value = 0;
            emit('avatarSetAsMain', {
                success: !!res.data.success,
                message: res.data.success ? 'Avatar has been set as main!' : 'Error',
            });
            router.reload({ only: ['auth.user'] });
        })
        .catch(() => {
            emit('avatarSetAsMain', {
                success: false,
                message: 'Error setting avatar as main',
            });
        });
}

function deleteAvatar(avatar: Avatar) {
    axios
        .delete('/profile/settings/delete-avatar', { params: { id: avatar.id } })
        .then((res) => {
            visibleAvatars.value = res.data.avatars;
            currentIndex.value = 0;
            emit('avatarSetAsMain', {
                success: !!res.data.success,
                message: res.data.success ? 'Avatar has been deleted!' : 'Error',
            });
            router.reload({ only: ['auth.user'] });
        })
        .catch(() => {
            emit('avatarSetAsMain', {
                success: false,
                message: 'Error deleting avatar',
            });
        });
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

watch(
    () => props.avatars,
    (newAvatars) => {
        visibleAvatars.value = newAvatars ?? [];
        currentIndex.value = 0;
    },
);
</script>

<template>
    <div v-if="total" class="relative flex w-full items-center justify-center">
        <div class="relative w-5/6 overflow-hidden rounded-md">
            <div class="flex transition-transform duration-500 ease-in-out" :style="{ transform: `translateX(${slideX}%)` }">
                <div v-for="(avatar, index) in visibleAvatars" :key="avatar.id" class="relative aspect-square w-full flex-shrink-0">
                    <img :src="avatar.path" alt="avatar" class="h-full w-full rounded-md object-cover" />

                    <button
                        class="absolute top-2 right-2 cursor-pointer rounded-full border border-gray-300 bg-white/70 p-2 shadow-md transition hover:bg-white dark:border-gray-600 dark:bg-gray-700/70 dark:hover:bg-gray-700"
                        @click.stop="toggleMenu(index)"
                    >
                        <MoreVertical class="h-5 w-5 text-gray-700 dark:text-gray-300" />
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
                class="absolute top-1/2 left-2 z-10 -translate-y-1/2 cursor-pointer rounded-full border border-gray-300 bg-white/70 p-3 shadow-md transition hover:bg-white dark:border-gray-600 dark:bg-gray-700/70 dark:hover:bg-gray-700"
            >
                <span class="text-2xl font-bold text-gray-800 dark:text-gray-100">‹</span>
            </button>

            <button
                @click="next"
                class="absolute top-1/2 right-2 z-10 -translate-y-1/2 cursor-pointer rounded-full border border-gray-300 bg-white/70 p-3 shadow-md transition hover:bg-white dark:border-gray-600 dark:bg-gray-700/70 dark:hover:bg-gray-700"
            >
                <span class="text-2xl font-bold text-gray-800 dark:text-gray-100">›</span>
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
    <div v-else class="relative flex w-full items-center justify-center">
        <div class="relative flex aspect-square w-5/6 items-center justify-center overflow-hidden rounded-md bg-gray-200 dark:bg-gray-700">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-20 w-20 text-gray-500 dark:text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5.121 17.804A9 9 0 1118.879 17.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                />
            </svg>
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
