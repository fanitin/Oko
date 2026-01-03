<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import MainButtonComponent from '@/components/custom/MainButtonComponent.vue';
import { router } from '@inertiajs/vue3';
import { UploadCloud } from 'lucide-vue-next';

interface Avatar {
    id: number;
    path: string;
    is_main: boolean;
}

const fileInput = ref<HTMLInputElement | null>(null);
const emit = defineEmits<{
    (e: 'avatarUploaded', payload: { success: boolean; message: string, avatars?: Avatar[] }): void
}>();

function openFileDialog() {
    fileInput.value?.click();
}

function onFileSelected(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const file = target.files[0];
        const formData = new FormData();
        formData.append('avatar', file);
        console.log(Array.from(formData.entries()));
        axios
            .post('/profile/settings/upload-avatar', formData)
            .then((response) => {
                emit('avatarUploaded', {
                    success: !!response.data.success,
                    message: response.data.success ? 'Profile picture updated!' : 'Loading error',
                    avatars: response.data.avatars
                });
                router.reload({ only: ['auth.user'] });
            })
            .catch(() => {
                emit('avatarUploaded', { success: false, message: 'Network error' });
            });
    }
}
</script>

<template>
    <div>
        <input type="file" ref="fileInput" @change="onFileSelected" accept="image/*" class="hidden" />
        <button
            @click="openFileDialog"
            class="w-full flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl text-center cursor-pointer transition-all duration-300 hover:border-blue-500 dark:hover:border-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700/50"
        >
            <UploadCloud class="h-8 w-8 text-gray-400 dark:text-gray-500 mb-2" />
            <span class="font-semibold text-gray-700 dark:text-gray-300">Add profile picture</span>
            <span class="text-sm text-gray-500 dark:text-gray-400">Click to upload</span>
        </button>
    </div>
</template>
