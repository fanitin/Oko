<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { MoreVertical, ChevronLeft, ChevronRight, ImageOff, Trash2, Star } from 'lucide-vue-next';
import { computed, defineProps, onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface Avatar {
    id: number;
    path: string;
    is_main: boolean;
}

const emit = defineEmits<{
    (e: 'avatarSetAsMain', payload: { success: boolean; message: string }): void;
}>();

const props = withDefaults(
    defineProps<{
        avatars?: Avatar[];
        show_tools?: boolean;
    }>(),
    {
        show_tools: true,
    }
);

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
    <div class="relative w-full group">
        <div v-if="total > 0" class="relative w-full overflow-hidden rounded-2xl aspect-square shadow-lg">
            <div class="flex transition-transform duration-500 ease-in-out" :style="{ transform: `translateX(${slideX}%)` }">
                <div v-for="(avatar, index) in visibleAvatars" :key="avatar.id" class="relative w-full flex-shrink-0">
                    <img :src="avatar.path" alt="User Avatar" class="h-full w-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>

                    <div v-if="avatar.is_main && show_tools" class="absolute top-3 left-3 bg-yellow-400/90 text-white rounded-full p-1.5 shadow-lg">
                        <Star class="h-4 w-4" fill="currentColor" />
                    </div>

                    <button
                        v-if="show_tools"
                        class="absolute top-2 right-2 z-20 p-2 rounded-full bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm shadow-md transition-all hover:bg-white dark:hover:bg-gray-800"
                        @click.stop="toggleMenu(index)"
                    >
                        <MoreVertical class="h-5 w-5 text-gray-800 dark:text-gray-200" />
                    </button>

                    <transition name="fade">
                        <div
                            v-if="showMenu === index"
                            id="avatar-menu"
                            class="absolute top-14 right-2 z-50 w-48 rounded-xl bg-white/80 dark:bg-gray-800/90 backdrop-blur-lg shadow-2xl p-2 space-y-1"
                        >
                            <button @click="setMain(avatar)" class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-left text-sm font-medium text-gray-800 dark:text-gray-200 hover:bg-gray-200/70 dark:hover:bg-gray-700/70 transition-colors">
                                <Star class="h-4 w-4" />
                                <span>Set as main</span>
                            </button>
                            <button @click="deleteAvatar(avatar)" class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-left text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-100/70 dark:hover:bg-red-500/20 transition-colors">
                                <Trash2 class="h-4 w-4" />
                                <span>Delete</span>
                            </button>
                        </div>
                    </transition>
                </div>
            </div>

            <template v-if="total > 1">
                <button @click="prev" class="absolute top-1/2 left-3 z-10 -translate-y-1/2 p-2 rounded-full bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm shadow-lg transition-all opacity-0 group-hover:opacity-100 hover:scale-110 hover:bg-white/70 dark:hover:bg-gray-800/70">
                    <ChevronLeft class="h-6 w-6 text-gray-800 dark:text-gray-200" />
                </button>
                <button @click="next" class="absolute top-1/2 right-3 z-10 -translate-y-1/2 p-2 rounded-full bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm shadow-lg transition-all opacity-0 group-hover:opacity-100 hover:scale-110 hover:bg-white/70 dark:hover:bg-gray-800/70">
                    <ChevronRight class="h-6 w-6 text-gray-800 dark:text-gray-200" />
                </button>
            </template>

            <div v-if="total > 1" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex space-x-2">
                <button
                    v-for="(_, idx) in total"
                    :key="idx"
                    @click="currentIndex = idx"
                    :class="['h-2 w-2 rounded-full transition-all duration-300', currentIndex === idx ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/80']"
                ></button>
            </div>
        </div>

        <div v-else class="relative flex w-full items-center justify-center overflow-hidden rounded-2xl bg-gray-200 dark:bg-gray-700/50 aspect-square">
            <div class="text-center text-gray-500 dark:text-gray-400">
                <ImageOff class="h-16 w-16 mx-auto" />
                <p class="mt-2 font-medium">No avatars yet</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s, transform 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-10px); }
.fade-enter-to, .fade-leave-from { opacity: 1; transform: translateY(0); }
</style>
