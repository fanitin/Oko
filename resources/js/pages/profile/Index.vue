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
        <div class="flex justify-center mt-10">
            <div class="w-full max-w-5xl flex gap-8">

                <!-- Левая колонка: профиль/настройки -->
                <div class="flex flex-col w-1/3 space-y-6">
                    <!-- Никнейм -->
                    <div class="flex flex-col space-y-2">
                        <p class="text-2xl font-semibold">Fanitin</p>
                        <button class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 text-sm">Изменить никнейм</button>
                    </div>

                    <!-- Настройки -->
                    <div class="flex flex-col space-y-2">
                        <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Настройка 1</button>
                        <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Настройка 2</button>
                        <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Настройка 3</button>
                    </div>
                </div>

                <!-- Правая колонка: аватарка -->
                <div class="flex flex-col w-2/3 items-center space-y-4">
                    <AvatarSlider :avatars="user.avatars" />
                    <AddAvatarButton @avatarUploaded="handleAvatarUploaded" />
                </div>
            </div>
        </div>
    </AppLayout>

    <MainPopup ref="popupRef" />
</template>
