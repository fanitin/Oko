<script setup lang="ts">
import MainPopup from '@/components/custom/MainPopup.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import ChatNavBar from "@/components/custom/chat/ChatNavBar.vue";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chat',                          //change later
        href: '/chat',
    },
];

interface Avatar {
    id: number;
    path: string;
    is_main: boolean;
}

const props = defineProps<{
    chatWith: {
        id: number;
        username: string;
        email: string;
        avatars?: Avatar[];
        main_avatar: Avatar;
    };
    chat: {
        id: number;
        is_group: boolean;
        is_self: boolean;
        created_at: Date;
        updated_at: Date;
    };
}>();

const popupRef = ref<typeof MainPopup>();
</script>

<template>
    <Head :title="props.chatWith.username" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <ChatNavBar :user="props.chatWith" :chat="props.chat"/>


    </AppLayout>

    <MainPopup ref="popupRef" />
</template>
