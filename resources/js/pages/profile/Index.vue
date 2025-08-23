<script setup lang="ts">
import AddAvatarButton from '@/components/custom/profile/AddAvatarButton.vue';
import AvatarSlider from '@/components/custom/profile/AvatarSlider.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import MainPopup from '@/components/custom/MainPopup.vue';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile',
        href: '/profile',
    },
];

const popupRef = ref<typeof MainPopup>();

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
    };
}>();

function handleAvatarUploaded(payload: { success: boolean; message: string }) {
    const type = payload.success ? 'success' : 'error';
    popupRef.value?.show(payload.message, type);
}
</script>

<template>
    <Head title="Profile" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container flex justify-center">
            <div>
                <p class="text-4xl">Profile</p>
                <AvatarSlider :avatars="user.avatars" />
                <AddAvatarButton @avatarUploaded="handleAvatarUploaded"/>
            </div>
        </div>
    </AppLayout>

    <MainPopup ref="popupRef" />
</template>
