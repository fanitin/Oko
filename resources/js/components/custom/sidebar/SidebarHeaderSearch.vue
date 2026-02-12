<script setup lang="ts">
import { useSidebar } from '@/components/ui/sidebar';
import { Search, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { sidebarState } from '@/lib/custom/states/sidebarState';
import { debounce } from 'lodash-es';

const { state, toggleSidebar } = useSidebar();
const searchQuery = ref('');

function onSearchClick() {
    if (state.value === 'collapsed') {
        toggleSidebar();
        return;
    }
}

function clearSearch() {
    searchQuery.value = '';
    sidebarState.clearSearch();
}

const debouncedFilterChats = debounce((query: string) => {
    sidebarState.filterChats(query);
}, 300);

watch(searchQuery, (newQuery) => {
    debouncedFilterChats(newQuery);
});
</script>

<template>
    <div class="flex w-full items-center justify-center">
        <button
            @click="onSearchClick"
            :class="[
                'flex items-center justify-center rounded-md p-2 text-gray-700 transition hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700',
                state === 'collapsed' ? 'h-12 w-12' : ''
            ]"
        >
            <Search :class="state === 'collapsed' ? 'h-6 w-6' : 'h-5 w-5'" />
        </button>

        <div
            v-if="state !== 'collapsed'"
            :class="[
                'relative ml-2 flex-1 transition-all duration-300',
                state === 'collapsed' ? 'max-w-0 opacity-0 overflow-hidden' : 'max-w-full opacity-100'
            ]"
        >
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Search chats, messages..."
                class="w-full rounded-md border border-gray-300 bg-gray-100 py-2 pl-3 pr-10 text-sm text-gray-900 placeholder-gray-500 transition focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400 dark:focus:border-purple-400 dark:focus:ring-purple-900"
            />

            <button
                v-if="searchQuery"
                @click="clearSearch"
                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full p-1 text-gray-500 transition hover:bg-gray-200 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300"
            >
                <X class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>

<style scoped></style>
