<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex flex-col gap-3 p-2">

        <Link :href="route('home')" class="flex items-center gap-2">
            <div class="flex aspect-square h-8 w-8 items-center justify-center rounded-md bg-gray-300 dark:bg-gray-700">
                <AppLogo />
            </div>
            <span class="font-semibold text-gray-900 dark:text-gray-100 truncate">OKO</span>
        </Link>
    </div>
</template>

<style scoped>
</style>
