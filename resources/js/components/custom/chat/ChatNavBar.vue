<script setup lang="ts">
import { ref, defineProps } from 'vue';
import { Search, MoreVertical } from 'lucide-vue-next';

interface Avatar {
    id: number;
    path: string;
    is_main: boolean;
}

const props = defineProps<{
    user: {
        id: number;
        username: string;
        email: string;
        avatars?: Avatar[];
        main_avatar: Avatar;
    };
}>();

function onAvatarClick() {
    console.log('Avatar clicked');
}

function onSearchClick() {
    console.log('Search clicked');
}

function onMenuClick() {
    console.log('Menu clicked');
}
</script>

<template>
    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 p-2">
        <div class="flex items-center gap-2 cursor-pointer" @click="onAvatarClick">
            <img
                v-if="props.user.main_avatar.path"
                :src="props.user.main_avatar.path"
                alt="avatar"
                class="h-10 w-10 rounded-full border-2 border-gray-300 dark:border-gray-600 object-cover"
            />
            <div v-else class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                <span class="font-bold text-gray-600 dark:text-gray-300">{{ props.user.username.charAt(0) }}</span>
            </div>
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ props.user.username }}</span>
        </div>


        <div class="flex items-center gap-3">
            <button
                @click="onSearchClick"
                class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition"
            >
                <Search class="h-5 w-5 text-gray-700 dark:text-gray-300"/>
            </button>

            <button
                @click="onMenuClick"
                class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 transition"
            >
                <MoreVertical class="h-5 w-5 text-gray-700 dark:text-gray-300"/>
            </button>
        </div>
    </div>
</template>

<style scoped>
</style>
