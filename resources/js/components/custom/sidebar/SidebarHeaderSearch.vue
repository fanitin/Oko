<script setup lang="ts">
import { useSidebar } from '@/components/ui/sidebar';
import { Search } from 'lucide-vue-next';
import { ref } from 'vue';

const { state, toggleSidebar } = useSidebar();
const searchQuery = ref('');

function onSearch() {
    if (state.value === 'collapsed') {
        toggleSidebar();
        return;
    }

    console.log('Searching for:', searchQuery.value);
}
</script>

<template>
    <div class="flex w-full items-center">
        <button
            @click="onSearch"
            class="flex items-center justify-center rounded-md p-2 text-gray-700 transition hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700"
        >
            <Search />
        </button>

        <input
            v-if="state !== 'collapsed'"
            v-model="searchQuery"
            @keyup="onSearch"
            type="text"
            placeholder="Search..."
            class="ml-2 w-full flex-1 rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-900 placeholder-gray-500 transition focus:border-blue-500 focus:ring focus:ring-blue-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400 dark:focus:border-blue-400 dark:focus:ring-blue-900"
        />
    </div>
</template>

<style scoped></style>
