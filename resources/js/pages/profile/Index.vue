<script setup lang="ts">
import MainPopup from '@/components/custom/MainPopup.vue';
import AddAvatarButton from '@/components/custom/profile/AddAvatarButton.vue';
import AvatarSlider from '@/components/custom/profile/AvatarSlider.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Settings } from 'lucide-vue-next';
import { ref } from 'vue';
import UsernameProfileComponent from '@/components/custom/profile/UsernameProfileComponent.vue';
import MainButtonComponent from '@/components/custom/MainButtonComponent.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile',
        href: '/profile',
    },
];

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

const popupRef = ref<typeof MainPopup>();

function handleAvatarUploaded(payload: { success: boolean; message: string }) {
    const type = payload.success ? 'success' : 'error';
    popupRef.value?.show(payload.message, type);
}

function handleSetAsMain(payload: { success: boolean; message: string }) {
    const type = payload.success ? 'success' : 'error';
    popupRef.value?.show(payload.message, type);
}

function handleUsernameChange(payload: { type: string; message: string }) {
    popupRef.value?.show(payload.message, payload.type);
}
</script>

<template>
    <Head title="Profile" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mt-10 flex justify-center">
            <div class="flex w-full max-w-5xl flex-col gap-8 md:flex-row">
                <div class="flex flex-col space-y-6 md:w-1/3">
                    <UsernameProfileComponent :user="user" @popUpRefChange="handleUsernameChange"/>

                    <div class="mt-4">
                        <Link
                            href="/settings/profile"
                            class="flex items-center gap-2 rounded-md bg-gray-100 px-4 py-2 transition hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700"
                        >
                            <Settings class="h-5 w-5" />
                            <span>Settings</span>
                        </Link>
                    </div>

                    <div class="mt-2">
                        <form :action="route('logout')" method="POST">
                            @csrf
                            <MainButtonComponent type="secondary" size="md" class="w-full" button-type="submit">
                                Logout
                            </MainButtonComponent>
                        </form>
                    </div>
                </div>

                <div class="flex flex-col items-center space-y-4 md:w-2/3">
                    <AvatarSlider :avatars="props.user.avatars" @avatarSetAsMain="handleSetAsMain" />
                    <AddAvatarButton @avatarUploaded="handleAvatarUploaded" />
                </div>
            </div>
        </div>
    </AppLayout>

    <MainPopup ref="popupRef" />
</template>
