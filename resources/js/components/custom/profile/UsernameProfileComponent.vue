<script setup lang="ts">
import MainButtonComponent from '@/components/custom/MainButtonComponent.vue';
import ToolTip from '@/components/custom/ToolTip.vue';
import axios from 'axios';
import { defineProps, ref } from 'vue';
import { Edit3, Check, X } from 'lucide-vue-next';

const props = withDefaults(
    defineProps<{
        user: {
            id: number;
            username: string;
            email: string;
        };
        is_self_profile?: boolean;
    }>(),
    {
        is_self_profile: true,
    }
);

const emit = defineEmits<{
    (e: 'popUpRefChange', payload: { type: string; message: string }): void;
}>();

const editing = ref(false);
const username = ref(props.user.username);
const tempUsername = ref(username.value);

function saveUsername() {
    if (tempUsername.value.trim() === '') {
        emit('popUpRefChange', {
            type: 'error',
            message: 'Username cannot be empty',
        });
        return;
    }

    if (tempUsername.value.trim() === username.value) {
        editing.value = false;
        return;
    }
    axios
        .post('/profile/settings/update-username', { username: tempUsername.value })
        .then((res) => {
            if (res.data.success) {
                username.value = tempUsername.value;
                editing.value = false;
                emit('popUpRefChange', {
                    type: 'success',
                    message: res.data.message,
                });
            } else {
                emit('popUpRefChange', {
                    type: 'error',
                    message: res.data.message,
                });
            }
        })
        .catch((err) => {
            const message = err.response?.data?.message || 'Error updating username';
            emit('popUpRefChange', {
                type: 'error',
                message,
            });
        });
}

function cancelEditing() {
    editing.value = false;
}

const copyEmail = async (email: string) => {
    try {
        await navigator.clipboard.writeText(email);
        emit('popUpRefChange', {
            type: 'success',
            message: 'Email copied to clipboard!',
        });
    } catch (error) {
        emit('popUpRefChange', {
            type: 'error',
            message: 'Failed to copy email.',
        });
    }
};

const copyUsername = async (name: string) => {
    try {
        await navigator.clipboard.writeText(name);
        emit('popUpRefChange', {
            type: 'success',
            message: 'Username copied to clipboard!',
        });
    } catch (error) {
        emit('popUpRefChange', {
            type: 'error',
            message: 'Failed to copy username.',
        });
    }
};
</script>

<template>
    <div class="flex flex-col space-y-3">
        <div class="relative h-10">
            <transition name="fade-fast">
                <div v-if="!editing" class="absolute inset-0 flex items-center gap-2">
                    <ToolTip v-if="!is_self_profile" text="Click to copy username" class="w-full">
                        <p
                            @click="copyUsername(username)"
                            class="text-2xl font-semibold text-gray-800 dark:text-gray-200 cursor-pointer select-all p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700/70 transition-colors"
                        >
                            {{ username }}
                        </p>
                    </ToolTip>
                    <div v-else class="group flex items-center gap-2">
                        <p class="text-2xl font-semibold text-gray-800 dark:text-gray-200">{{ username }}</p>
                        <button
                            @click="editing = true"
                            class="p-1 rounded-full text-gray-500 dark:text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-gray-200 dark:hover:bg-gray-700"
                        >
                            <Edit3 class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </transition>

            <transition name="fade-fast">
                <div v-if="editing && is_self_profile" class="absolute inset-0 flex items-center gap-2">
                    <input
                        v-model="tempUsername"
                        class="h-full flex-grow rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 text-lg text-gray-900 dark:text-gray-100 transition-all duration-200 focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter new username"
                        @keyup.enter="saveUsername"
                        @keyup.esc="cancelEditing"
                    />
                    <MainButtonComponent type="primary" size="sm" @click="saveUsername" class="!p-2">
                        <Check class="h-4 w-4" />
                    </MainButtonComponent>
                    <MainButtonComponent type="secondary" size="sm" @click="cancelEditing" class="!p-2">
                        <X class="h-4 w-4" />
                    </MainButtonComponent>
                </div>
            </transition>
        </div>

        <ToolTip text="Click to copy email" class="w-full">
            <p
                class="text-sm text-gray-500 dark:text-gray-400 cursor-pointer select-all p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700/70 transition-colors"
                @click="copyEmail(props.user.email)"
            >
                {{ props.user.email }}
            </p>
        </ToolTip>
    </div>
</template>

<style scoped>
.fade-fast-enter-active,
.fade-fast-leave-active {
    transition: opacity 0.15s ease-in-out;
}
.fade-fast-enter-from,
.fade-fast-leave-to {
    opacity: 0;
}
</style>
