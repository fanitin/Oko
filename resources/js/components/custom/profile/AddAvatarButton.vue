<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import MainButtonComponent from '@/components/custom/MainButtonComponent.vue';
import { router } from '@inertiajs/vue3';

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
        <input type="file" ref="fileInput" class="hidden" @change="onFileSelected" accept="image/*" />
        <MainButtonComponent @click="openFileDialog">
            Add profile picture
        </MainButtonComponent>
    </div>
</template>
