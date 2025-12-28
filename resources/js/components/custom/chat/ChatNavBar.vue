<script setup lang="ts">
import { computed } from 'vue'
import { Search, MoreVertical } from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3'

const props = defineProps<{
    chat: {
        type: 'self' | 'private' | 'group'
        header: {
            title: string
            avatar: string | null
            link: {
                route: string
                params: any[]
            }
        }
    }
}>()

const safeHeader = computed(() => ({
    title: props.chat.header.title,
    avatar: props.chat.header.avatar,
    link: props.chat.header.link,
}))

const firstLetter = computed(() =>
    safeHeader.value.title?.charAt(0).toUpperCase() ?? '?'
)

function onSearchClick() {
    console.log('Search clicked')
}

function onMenuClick() {
    console.log('Menu clicked')
}
</script>
<template>
    <div
        class="flex items-center justify-between border-b border-gray-200 p-2 dark:border-gray-700"
    >
        <Link
            :href="route(safeHeader.link.route, safeHeader.link.params)"
        >
            <div class="flex cursor-pointer items-center gap-2">
                <img
                    v-if="safeHeader.avatar"
                    :src="safeHeader.avatar"
                    alt="avatar"
                    class="h-10 w-10 rounded-full border-2 border-gray-300 object-cover dark:border-gray-700"
                />

                <div
                    v-else
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-300 dark:bg-gray-600"
                >
                    <span class="font-bold text-gray-700 dark:text-gray-200">
                        {{ firstLetter }}
                    </span>
                </div>

                <span class="font-medium text-gray-900 dark:text-gray-100">
                    {{ safeHeader.title }}
                </span>
            </div>
        </Link>

        <div class="flex items-center gap-3">
            <button
                @click="onSearchClick"
                class="rounded-md p-2 transition hover:bg-gray-200 dark:hover:bg-gray-700"
            >
                <Search class="h-5 w-5 text-gray-700 dark:text-gray-300" />
            </button>

            <button
                @click="onMenuClick"
                class="rounded-md p-2 transition hover:bg-gray-200 dark:hover:bg-gray-700"
            >
                <MoreVertical class="h-5 w-5 text-gray-700 dark:text-gray-300" />
            </button>
        </div>
    </div>
</template>
