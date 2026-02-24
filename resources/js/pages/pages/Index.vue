<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem, type User } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash-es';
import axios from 'axios';
import {
    Search,
    MessageSquarePlus,
    Users,
    MessageCircle,
    Mail,
    AtSign,
    Loader2,
    UserPlus,
    Clock,
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Home',
        href: '/',
    },
];

const props = defineProps<{
    recentChats: Array<{
        id: number;
        name: string;
        avatar: string | null;
        type: 'self' | 'private' | 'group';
        lastMessage: {
            body: string;
            created_at: string;
        } | null;
    }>;
}>();

const searchQuery = ref('');
const searchResults = ref<User[]>([]);
const isSearching = ref(false);
const hasSearched = ref(false);

const debouncedSearch = debounce(async (query: string) => {
    if (!query.trim()) {
        searchResults.value = [];
        isSearching.value = false;
        hasSearched.value = false;
        return;
    }

    try {
        const response = await axios.get('/users/search', {
            params: { q: query },
        });
        searchResults.value = response.data.users;
    } catch (error) {
        console.error('Search error:', error);
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
}, 300);

watch(searchQuery, (newQuery) => {
    if (newQuery.trim()) {
        isSearching.value = true;
        hasSearched.value = true;
    }
    debouncedSearch(newQuery);
});

const startChat = (chatId: number, userId: number) => {
    router.get(route('chat.show', { chat: chatId, user: userId }));
};

const getInitials = (name: string) => {
    if (!name) return '?';
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    if (days === 0) {
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
    } else if (days === 1) {
        return 'Yesterday';
    } else if (days < 7) {
        return date.toLocaleDateString('en-US', { weekday: 'short' });
    }
    return date.toLocaleDateString('en-US', { day: 'numeric', month: 'short' });
};
</script>

<template>
    <Head title="Home" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-col gap-4 p-4 md:gap-6 md:p-6">
            <div class="grid min-h-0 flex-1 grid-cols-1 gap-4 md:gap-6 lg:grid-cols-2">
                <Card class="flex flex-col border-gray-200 dark:border-gray-800">
                    <CardHeader class="flex-shrink-0">
                        <CardTitle class="flex items-center gap-2 text-gray-900 dark:text-gray-100">
                            <UserPlus class="h-5 w-5 text-purple-500" />
                            Find Users
                        </CardTitle>
                        <CardDescription class="text-gray-500 dark:text-gray-400">
                            Search by email or username to start a new chat
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex min-h-0 flex-1 flex-col">
                        <div class="relative flex-shrink-0">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Enter email or @username..."
                                class="bg-gray-50 pl-10 dark:border-gray-700 dark:bg-gray-900"
                            />
                            <Loader2
                                v-if="isSearching"
                                class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-purple-500"
                            />
                        </div>

                        <div
                            v-if="searchResults.length > 0"
                            class="mt-4 flex-1 space-y-2 overflow-y-auto pr-1"
                        >
                            <div
                                v-for="user in searchResults"
                                :key="user.id"
                                class="flex items-center justify-between rounded-lg bg-gray-50 p-3 transition hover:bg-gray-100 dark:bg-gray-800/50 dark:hover:bg-gray-800"
                            >
                                <div class="flex min-w-0 flex-1 items-center gap-3">
                                    <Avatar class="h-10 w-10 flex-shrink-0">
                                        <AvatarImage v-if="user.avatar" :src="user.avatar" />
                                        <AvatarFallback class="bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300">
                                            {{ getInitials(user.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-medium text-gray-900 dark:text-gray-100">
                                            {{ user.name }}
                                        </p>
                                        <p class="truncate text-sm text-gray-500 dark:text-gray-400">
                                            {{ user.email }}
                                        </p>
                                    </div>
                                </div>
                                <Button
                                    size="sm"
                                    @click="startChat(user.chatId, user.id)"
                                    class="ml-2 flex-shrink-0 bg-purple-600 hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-500"
                                >
                                    <MessageSquarePlus class="h-4 w-4 md:mr-1" />
                                    <span class="hidden md:inline">Message</span>
                                </Button>
                            </div>
                        </div>

                        <div
                            v-else-if="hasSearched && !isSearching && searchQuery"
                            class="mt-4 flex flex-1 flex-col items-center justify-center py-8 text-center"
                        >
                            <Users class="mb-2 h-12 w-12 text-gray-300 dark:text-gray-600" />
                            <p class="text-gray-500 dark:text-gray-400">No users found</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500">
                                Try a different email or username
                            </p>
                        </div>

                        <div
                            v-else-if="!searchQuery"
                            class="mt-4 flex flex-1 flex-col items-center justify-center py-8 text-center"
                        >
                            <div class="mb-3 rounded-full bg-purple-100 p-4 dark:bg-purple-900/30">
                                <Search class="h-8 w-8 text-purple-500" />
                            </div>
                            <p class="text-gray-600 dark:text-gray-300">Start typing to search</p>
                            <div class="mt-2 flex gap-2 text-xs text-gray-400 dark:text-gray-500">
                                <span class="flex items-center gap-1">
                                    <Mail class="h-3 w-3" /> email
                                </span>
                                <span>or</span>
                                <span class="flex items-center gap-1">
                                    <AtSign class="h-3 w-3" /> username
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Chats Section -->
                <Card class="flex flex-col border-gray-200 dark:border-gray-800">
                    <CardHeader class="flex flex-shrink-0 flex-row items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2 text-gray-900 dark:text-gray-100">
                                <Clock class="h-5 w-5 text-purple-500" />
                                Recent Chats
                            </CardTitle>
                            <CardDescription class="text-gray-500 dark:text-gray-400">
                                Your latest active conversations
                            </CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent class="min-h-0 flex-1 overflow-y-auto pr-1">
                        <div v-if="props.recentChats?.length > 0" class="space-y-2">
                            <Link
                                v-for="chat in props.recentChats"
                                :key="chat.id"
                                :href="`/chat/${chat.id}`"
                                class="flex items-center gap-3 rounded-lg bg-gray-50 p-3 transition hover:bg-gray-100 dark:bg-gray-800/50 dark:hover:bg-gray-800"
                            >
                                <Avatar class="h-10 w-10 flex-shrink-0 md:h-12 md:w-12">
                                    <AvatarImage v-if="chat.avatar" :src="chat.avatar" />
                                    <AvatarFallback class="bg-gradient-to-br from-purple-500 to-violet-600 text-white">
                                        {{ getInitials(chat.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="truncate font-medium text-gray-900 dark:text-gray-100">
                                            {{ chat.name }}
                                        </p>
                                        <span
                                            v-if="chat.lastMessage"
                                            class="flex-shrink-0 text-xs text-gray-400 dark:text-gray-500"
                                        >
                                            {{ formatTime(chat.lastMessage.created_at) }}
                                        </span>
                                    </div>
                                    <p
                                        v-if="chat.lastMessage"
                                        class="truncate text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ chat.lastMessage.body }}
                                    </p>
                                    <p v-else class="text-sm italic text-gray-400 dark:text-gray-500">
                                        No messages yet
                                    </p>
                                </div>
                            </Link>
                        </div>

                        <div v-else class="flex h-full flex-col items-center justify-center py-8 text-center">
                            <div class="mb-3 rounded-full bg-gray-100 p-4 dark:bg-gray-800">
                                <MessageCircle class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                            </div>
                            <p class="text-gray-600 dark:text-gray-300">No active chats</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500">
                                Find a user and start chatting
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(75, 85, 99, 0.5);
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.7);
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background-color: rgba(75, 85, 99, 0.7);
}
</style>

