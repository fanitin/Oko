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
const avatars = ref<Avatar[]>(props.user.avatars ?? []);

function handleAvatarUploaded(payload: { success: boolean; message: string, avatars?: Avatar[] }) {
    const type = payload.success ? 'success' : 'error';
    popupRef.value?.show(payload.message, type);
    if(payload.avatars != null){
        avatars.value = payload.avatars;
    }
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
                            :href="route('profile.edit')"
                            class="flex items-center gap-2 rounded-md bg-gray-100 px-4 py-2 transition hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700"
                        >
                            <Settings class="h-5 w-5" />
                            <span>Settings</span>
                        </Link>
                    </div>

                    <div class="mt-2">
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="w-full rounded-2xl bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 transition duration-200 shadow-md hover:shadow-lg active:scale-95"
                        >
                            Logout
                        </Link>
                    </div>
                </div>

                <div class="flex flex-col items-center space-y-4 md:w-2/3">
                    <AvatarSlider :avatars="avatars" @avatarSetAsMain="handleSetAsMain" />
                    <AddAvatarButton @avatarUploaded="handleAvatarUploaded" />
                </div>
            </div>
        </div>
    </AppLayout>

    <MainPopup ref="popupRef" />
</template>
