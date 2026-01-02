<script setup lang="ts">
import { Check, CheckCheck } from 'lucide-vue-next';

defineProps<{
    message: {
        id: number;
        body: string;
        is_from_me: boolean;
        status?: 'delivered' | 'seen' | null;
        reply_to?: {
            id: number;
            body: string;
            user: { id: number; username: string };
        };
        user: {
            id: number;
            username: string;
            avatar: string | null;
        };
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
</script>

<template>
    <div :id="`message-wrapper-${message.id}`" class="message-wrapper rounded-lg px-4 transition-colors duration-500">
        <div class="flex items-end gap-3 py-2" :class="message.is_from_me ? 'justify-end' : 'justify-start'">
            <!-- Аватар -->
            <div v-if="!message.is_from_me && chatType === 'group'" class="h-8 w-8 flex-shrink-0 self-start">
                <img v-if="message.user.avatar" :src="message.user.avatar" class="h-8 w-8 rounded-full object-cover" alt="Аватар" />
                <div
                    v-else
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-300 text-sm font-bold text-gray-600 dark:bg-gray-600 dark:text-gray-200"
                >
                    {{ message.user.username[0].toUpperCase() }}
                </div>
            </div>

            <!-- Bubble -->
            <div
                class="relative max-w-[75%] rounded-2xl px-3.5 py-2.5 break-words shadow-md"
                :class="[
                    message.is_from_me
                        ? 'rounded-br-lg bg-blue-500 text-white dark:bg-purple-700'
                        : 'rounded-bl-lg bg-white text-gray-900 dark:bg-gray-700 dark:text-gray-100',
                ]"
            >
                <!-- Reply preview -->
                <div
                    v-if="message.reply_to"
                    class="mb-2 cursor-pointer rounded-md border-l-4 border-blue-400 bg-black/10 py-1.5 pr-3 pl-2 text-sm dark:border-purple-500 dark:bg-white/10"
                    @click="scrollToReply(message.reply_to.id)"
                >
                    <div class="font-semibold text-gray-800 dark:text-purple-300">{{ message.reply_to.user.username }}</div>
                    <div class="max-w-[200px] truncate text-gray-900 sm:max-w-[250px] dark:text-gray-300">
                        {{ message.reply_to.body }}
                    </div>
                </div>

                <!-- Username (групповые чаты) -->
                <div v-if="chatType === 'group' && !message.is_from_me" class="mb-1 text-xs font-bold text-purple-600 dark:text-purple-400">
                    {{ message.user.username }}
                </div>

                <!-- Message body -->
                <div class="text-base" :class="{ 'pr-8': message.is_from_me }">{{ message.body }}</div>

                <!-- Status для моих сообщений -->
                <div v-if="message.is_from_me" class="absolute right-2.5 bottom-1.5 flex items-center">
                    <CheckCheck v-if="message.status === 'seen'" class="h-4.5 w-4.5 text-cyan-300" />
                    <Check v-else-if="message.status === 'delivered'" class="h-4.5 w-4.5 text-gray-200" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.message-wrapper {
    --highlight-color: rgba(59, 130, 246, 0.2);
}

.dark .message-wrapper {
    --highlight-color: rgba(168, 85, 247, 0.2);
}

.message-wrapper.highlighted {
    animation: highlight-bg 3s ease-out;
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
