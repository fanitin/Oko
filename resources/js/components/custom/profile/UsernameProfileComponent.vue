<script setup lang="ts">
import ToolTip from '@/components/custom/ToolTip.vue';
import MainButtonComponent from '@/components/custom/MainButtonComponent.vue';
import { defineProps, ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
    user: {
        id: number;
        username: string;
        email: string;
    };
}>();
const emit = defineEmits<{
    (e: 'popUpRefChange', payload: { type: string; message: string }): void;
}>();

const editing = ref(false);
const username = ref(props.user.username);
const tempUsername = ref(username.value);

function saveUsername() {
    axios.post('/profile/settings/update-username', { username: tempUsername.value })
        .then(res => {
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
        .catch(err => {
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
</script>

<template>
    <div class="flex flex-col space-y-2">
        <div class="flex items-center gap-2">
            <template v-if="!editing">
                <ToolTip text="Click to change username">
                    <p
                        class="cursor-pointer text-2xl font-semibold"
                        @click="
                            editing = true;
                            tempUsername = username;
                        "
                    >
                        {{ username }}
                    </p>
                </ToolTip>
            </template>
            <template v-else>
                <input
                    v-model="tempUsername"
                    class="rounded border bg-white px-2 py-1 text-lg text-gray-900 dark:bg-gray-800 dark:text-gray-100"
                    placeholder="Enter new username"
                />
                <MainButtonComponent type="primary" size="sm" @click="saveUsername"> Save</MainButtonComponent>
                <MainButtonComponent type="secondary" size="sm" @click="cancelEditing"> Cancel</MainButtonComponent>
            </template>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ props.user.email }}</p>
    </div>
</template>

<style scoped></style>
