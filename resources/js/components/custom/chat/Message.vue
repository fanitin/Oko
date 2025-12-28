<script setup lang="ts">
defineProps<{
    message: {
        id: number;
        body: string;
        is_from_me: boolean;
        user: {
            id: number;
            username: string;
            avatar: string | null;
        };
    };
    chatType: 'self' | 'private' | 'group';
}>();
</script>

<template>
    <div class="flex gap-2" :class="message.is_from_me ? 'justify-end' : 'justify-start'">
        <!-- Аватар (НЕ от меня и ТОЛЬКО для group) -->
        <div
            v-if="!message.is_from_me && chatType === 'group'"
            class="h-8 w-8 flex-shrink-0"
        >
            <img
                v-if="message.user.avatar"
                :src="message.user.avatar"
                class="h-8 w-8 rounded-full object-cover"
            />
            <div
                v-else
                class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-400 text-xs text-white"
            >
                {{ message.user.username[0].toUpperCase() }}
            </div>
        </div>

        <!-- Bubble -->
        <div
            class="max-w-xs rounded-lg px-4 py-2 shadow-sm"
            :class="
                message.is_from_me
                    ? 'rounded-br-none bg-blue-500 text-white dark:bg-purple-600'
                    : 'rounded-bl-none bg-white text-gray-900 dark:bg-gray-600 dark:text-gray-100'
            "
        >
            <!-- Username (group + not me) -->
            <div
                v-if="chatType === 'group' && !message.is_from_me"
                class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-300"
            >
                {{ message.user.username }}
            </div>

            {{ message.body }}
        </div>
    </div>
</template>
