<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: '',
        href: '/',
    },
];
</script>

<template>
    <Head title="" />

    <AppLayout :breadcrumbs="breadcrumbs">

    </AppLayout>
</template>
