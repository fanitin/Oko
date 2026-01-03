<script setup lang="ts">
import { Check, CheckCheck } from 'lucide-vue-next';

defineProps<{
    message: {
        id: number;
        body: string;
        edited_at?: string | null;
        is_from_me: boolean;
        status?: 'delivered' | 'seen' | null;
        created_at: string;
        reply_to?: {
            id: number;
            body: string;
            user: {
                id: number;
                username: string;
            };
        };
        user: {
            id: number;
            username: string;
            avatar: string | null;
        };
        media?: {
            id: number;
            type: string;
            path: string;
            meta: Record<string, any>;
        }
    };
    chatType: 'self' | 'private' | 'group';
}>();

const scrollToReply = (id: number) => {
    const el = document.getElementById(`message-wrapper-${id}`);
    if (!el) return;

    el.scrollIntoView({ behavior: 'smooth', block: 'center' });

    el.classList.add('highlighted');
    setTimeout(() => el.classList.remove('highlighted'), 2500);
};

function formatTime(dateStr: string) {
    const date = new Date(dateStr);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <div :id="`message-wrapper-${message.id}`" class="message-wrapper px-2 py-1 transition-colors duration-500">
        <div class="flex items-end gap-2" :class="message.is_from_me ? 'justify-end' : 'justify-start'">
            <div v-if="!message.is_from_me && chatType === 'group'" class="h-9 w-9 flex-shrink-0 self-start">
                <img v-if="message.user.avatar" :src="message.user.avatar" class="h-9 w-9 rounded-full object-cover border border-gray-200 dark:border-gray-700" alt="Avatar" />
                <div
                    v-else
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-200 text-base font-bold text-gray-700 dark:bg-gray-700 dark:text-gray-200"
                >
                    {{ message.user.username[0].toUpperCase() }}
                </div>
            </div>

            <div
                class="relative max-w-[80%] rounded-2xl px-4 py-2.5 break-words shadow-lg"
                :class="[
                    message.is_from_me
                        ? 'rounded-br-lg bg-gradient-to-br from-blue-500 to-blue-400 text-white dark:from-purple-700 dark:to-purple-600'
                        : 'rounded-bl-lg bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-100',
                ]"
            >
                <div
                    v-if="message.reply_to"
                    class="mb-2 cursor-pointer rounded-md border-l-4 border-blue-400 bg-blue-50 py-1.5 pr-3 pl-2 text-sm dark:border-purple-500 dark:bg-purple-900/20"
                    @click="scrollToReply(message.reply_to.id)"
                >
                    <div class="font-semibold text-blue-700 dark:text-purple-300">{{ message.reply_to.user.username }}</div>
                    <div class="max-w-[220px] truncate text-gray-900 dark:text-gray-300">
                        {{ message.reply_to.body }}
                    </div>
                </div>

                <div v-if="chatType === 'group' && !message.is_from_me" class="mb-1 text-xs font-bold text-purple-600 dark:text-purple-400">
                    {{ message.user.username }}
                </div>

                <div class="text-base leading-relaxed pb-2 pr-3">{{ message.body }}</div>

                <div class="flex flex-col text-xs space-y-1" :class="message.is_from_me ? 'items-end' : 'items-start'">
                    <div class="flex items-center gap-1.5" :class="message.is_from_me ? 'text-gray-200' : 'text-gray-500 dark:text-gray-400'">
                        <span>{{ formatTime(message.created_at) }}</span>
                        <span v-if="message.is_from_me">
                            <CheckCheck v-if="message.status === 'seen'" class="h-4 w-4 text-cyan-300" />
                            <Check v-else-if="message.status === 'delivered'" class="h-4 w-4" />
                        </span>
                    </div>
                    <div v-if="message.edited_at" class="italic" :class="message.is_from_me ? 'text-gray-200/90' : 'text-gray-500/90 dark:text-gray-400/90'">
                        edited at {{ formatTime(message.edited_at) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.message-wrapper {
    --highlight-color: rgba(59, 130, 246, 0.18);
}
.dark .message-wrapper {
    --highlight-color: rgba(168, 85, 247, 0.18);
}
.message-wrapper.highlighted {
    animation: highlight-bg 2.5s ease-out;
}
@keyframes highlight-bg {
    0% {
        background-color: var(--highlight-color);
    }
    100% {
        background-color: transparent;
    }
}
</style>
