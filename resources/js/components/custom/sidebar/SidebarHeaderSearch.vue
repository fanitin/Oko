<script setup lang="ts">
import { useSidebar } from '@/components/ui/sidebar';
import { Search, X, Loader2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { sidebarState } from '@/lib/custom/states/sidebarState';
import { debounce } from 'lodash-es';

const { state, toggleSidebar } = useSidebar();
const searchQuery = ref('');
const isSearching = ref(false);

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
    isSearching.value = false;
}, 300);

watch(searchQuery, (newQuery) => {
    if (newQuery.trim()) {
        isSearching.value = true;
    }
    debouncedFilterChats(newQuery);
});
</script>

<template>
    <div class="flex w-full items-center justify-center">
        <button
            @click="onSearchClick"
            :class="[
                'flex items-center justify-center rounded-lg p-2 text-gray-700 transition hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700',
                state === 'collapsed' ? 'h-12 w-12' : ''
            ]"
        >
            <Search :class="state === 'collapsed' ? 'h-6 w-6' : 'h-5 w-5'" />
        </button>

        <div
            v-if="state !== 'collapsed'"
            class="relative ml-2 flex-1 transition-all duration-300"
        >
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Search chats..."
                class="w-full rounded-lg border border-gray-300 bg-gray-100 py-2.5 pl-4 pr-10 text-sm text-gray-900 placeholder-gray-500 transition focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400 dark:focus:border-purple-400 dark:focus:ring-purple-900"
            />

            <!-- Loading spinner -->
            <div
                v-if="isSearching"
                class="absolute right-3 top-1/2 -translate-y-1/2"
            >
                <Loader2 class="h-4 w-4 animate-spin text-purple-500" />
            </div>

            <!-- Clear button -->
            <button
                v-else-if="searchQuery"
                @click="clearSearch"
                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full p-1 text-gray-500 transition hover:bg-gray-200 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300"
            >
                <X class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>

<style scoped></style>
