<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';

const fileInput = ref<HTMLInputElement | null>(null);
const emit = defineEmits<{
    (e: 'avatarUploaded', payload: { success: boolean; message: string }): void
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
                    message: response.data.success ? 'Profile picture updated!' : 'Loading error'
                });
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

        <button
            class="rounded bg-emerald-500 px-4 py-2 text-white hover:bg-emerald-600 dark:bg-purple-600 dark:hover:bg-purple-700"
            @click="openFileDialog"
        >
            Add profile picture
        </button>
    </div>
</template>
