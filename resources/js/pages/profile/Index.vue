<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile',
        href: '/profile',
    },
];
</script>

<template>
    <Head title="321" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div>

        </div>
    </AppLayout>
</template>
