<script setup lang="ts">
import MainPopup from '@/components/custom/MainPopup.vue';
import AvatarSlider from '@/components/custom/profile/AvatarSlider.vue';
import UsernameProfileComponent from '@/components/custom/profile/UsernameProfileComponent.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { MessageSquare } from 'lucide-vue-next';
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
    chatID?: number;
}>();

const avatars = ref<Avatar[]>(props.user.avatars ?? []);

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
                        <AvatarSlider :avatars="avatars" @avatarSetAsMain="handleSetAsMain" :show_tools="false" />
                    </div>
                </div>

                <div class="flex w-full flex-col space-y-6 md:w-1/3">
                    <div class="rounded-3xl bg-white dark:bg-gray-800/50 shadow-lg p-6 transition-all duration-300">
                        <div class="mb-4">
                            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Profile</h2>
                            <p class="text-gray-500 dark:text-gray-400">User information</p>
                        </div>
                        <UsernameProfileComponent :user="user" @popUpRefChange="handleUsernameChange" :is_self_profile="false" />
                    </div>

                    <Link
                        :href="route('chat.show', { chat: props.chatID, user: props.user.id })"
                        class="group flex items-center justify-center gap-3 rounded-2xl bg-blue-500 dark:bg-blue-600 px-4 py-3 shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105 hover:bg-blue-600 dark:hover:bg-blue-700"
                    >
                        <MessageSquare class="h-5 w-5 text-white" />
                        <span class="font-semibold text-white">Go to the chat with {{user.username}}</span>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>

</template>
