<script setup lang="ts">
import AddAvatarButton from '@/components/custom/profile/AddAvatarButton.vue';
import AvatarSlider from '@/components/custom/profile/AvatarSlider.vue';
import UsernameProfileComponent from '@/components/custom/profile/UsernameProfileComponent.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Settings } from 'lucide-vue-next';
import { ref } from 'vue';
import { mainPopupState } from '@/lib/custom/states/mainPopupState';

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

const avatars = ref<Avatar[]>(props.user.avatars ?? []);

function handleAvatarUploaded(payload: { success: boolean; message: string; avatars?: Avatar[] }) {
    const type = payload.success ? 'success' : 'error';
    mainPopupState.show(payload.message, type);
    if (payload.avatars != null) {
        avatars.value = payload.avatars;
    }
}

function handleSetAsMain(payload: { success: boolean; message: string }) {
    const type = payload.success ? 'success' : 'error';
    mainPopupState.show(payload.message, type);
}

function handleUsernameChange(payload: { type: string; message: string }) {
    mainPopupState.show(payload.message, payload.type);
}
</script>

<template>
    <Head title="Profile" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mt-10 flex justify-center mx-6 md:mx-2">
            <div class="flex w-full max-w-5xl flex-col gap-8 md:flex-row">
                <!-- Left Column -->
                <div class="flex w-full flex-col items-center space-y-6 md:w-2/3">
                    <div
                        class="w-full rounded-3xl p-4 sm:p-6 bg-white dark:bg-gray-800/50 shadow-lg backdrop-blur-sm transition-all duration-300"
                    >
                        <AvatarSlider :avatars="avatars" @avatarSetAsMain="handleSetAsMain" />
                        <AddAvatarButton class="mt-4" @avatarUploaded="handleAvatarUploaded" />
                    </div>
                </div>

                <!-- Right Column -->
                <div class="flex w-full flex-col space-y-6 md:w-1/3">
                    <div class="rounded-3xl bg-white dark:bg-gray-800/50 shadow-lg p-6 transition-all duration-300">
                        <div class="mb-4">
                            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Profile</h2>
                            <p class="text-gray-500 dark:text-gray-400">Manage your profile information</p>
                        </div>
                        <UsernameProfileComponent :user="user" @popUpRefChange="handleUsernameChange" />
                    </div>

                    <Link
                        :href="route('profile.edit')"
                        class="group flex items-center gap-3 rounded-2xl bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-transparent px-4 py-3 shadow-md transition-all duration-300 hover:shadow-xl hover:scale-105"
                    >
                        <Settings class="h-5 w-5 text-gray-600 dark:text-gray-300 transition-transform duration-300 group-hover:rotate-45" />
                        <span class="font-semibold text-gray-800 dark:text-gray-200">Settings</span>
                    </Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="w-full rounded-2xl bg-red-500 py-3 px-4 font-semibold text-white shadow-lg transition-all duration-300 hover:bg-red-600 hover:shadow-red-500/40 active:scale-95"
                    >
                        Logout
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>

</template>
