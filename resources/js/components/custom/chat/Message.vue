<script setup lang="ts">
import { Check, CheckCheck, Download } from 'lucide-vue-next';
import { messageContextMenu } from '@/lib/custom/messageContextMenu';
import { onUnmounted, ref } from 'vue';
import MediaViewer from './MediaViewer.vue';

const props = defineProps<{
    message: {
        id: number;
        body: string;
        edited_at?: string | null;
        isFromMe: boolean;
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
        media?: Array<{
            id: number;
            type: 'image' | 'video' | 'file';
            path: string;
            meta: {
                original_name?: string;
                size?: number;
                mime_type?: string;
            };
        }>;
    };
    chatType: 'self' | 'private' | 'group';
}>();

const emit = defineEmits(['fetch-and-scroll']);
const elRef = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

const viewerOpen = ref(false)
const viewerIndex = ref(0)

const openMediaViewer = (index: number) => {
    viewerIndex.value = index
    viewerOpen.value = true
}

const scrollToReply = (id: number) => {
    const el = document.getElementById(`message-wrapper-${id}`);
    if (!el) {
        emit('fetch-and-scroll', id);
        return;
    }

    el.scrollIntoView({ behavior: 'smooth', block: 'center' });

    observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) {
                el.classList.add('highlighted');
                setTimeout(() => el.classList.remove('highlighted'), 2500);
                if (observer) observer.disconnect();
            }
        },
        { threshold: 0.5 },
    );

    observer.observe(el);
};

defineExpose({
    scrollToReply,
});

onUnmounted(() => {
    if (observer) observer.disconnect();
});

function formatTime(dateStr: string) {
    const date = new Date(dateStr);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function formatFileSize(bytes?: number) {
    if (!bytes) return 'Unknown'
    const mb = bytes / (1024 * 1024)
    if (mb < 1) return (bytes / 1024).toFixed(1) + ' KB'
    return mb.toFixed(2) + ' MB'
}

function openMenu(e: MouseEvent) {
    const menuWidth = 192;
    const menuHeight = 150;

    let x = e.clientX;
    let y = e.clientY;

    if (x + menuWidth > window.innerWidth) {
        x = window.innerWidth - menuWidth - 5;
    }

    if (y + menuHeight > window.innerHeight) {
        y = window.innerHeight - menuHeight - 5;
    }

    messageContextMenu.open = true;
    messageContextMenu.x = x;
    messageContextMenu.y = y;
    messageContextMenu.message = {
        id: props.message.id,
        body: props.message.body,
        isFromMe: props.message.isFromMe,
        user: {
            username: props.message.user.username,
        },
    };
}

const downloadFile = (media: any) => {
    const link = document.createElement('a')
    link.href = media.path
    link.download = media.meta.original_name || 'download'
    link.click()
}

</script>

<template>
    <div :id="`message-wrapper-${message.id}`" ref="elRef" class="message-wrapper px-2 py-1 transition-colors duration-500">
        <div class="flex items-end gap-2" :class="message.isFromMe ? 'justify-end' : 'justify-start'">
            <div v-if="!message.isFromMe && chatType === 'group'" class="h-9 w-9 flex-shrink-0 self-start">
                <img
                    v-if="message.user.avatar"
                    :src="message.user.avatar"
                    class="h-9 w-9 rounded-full border border-gray-200 object-cover dark:border-gray-700"
                    alt="Avatar"
                />
                <div
                    v-else
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-200 text-base font-bold text-gray-700 dark:bg-gray-700 dark:text-gray-200"
                >
                    {{ message.user.username[0].toUpperCase() }}
                </div>
            </div>

            <div
                class="relative max-w-[60%] rounded-2xl px-4 py-2.5 break-words shadow-md"
                :class="[
                    message.isFromMe
                        ? 'rounded-br-lg bg-gradient-to-br from-blue-500 to-blue-400 text-white dark:from-purple-700 dark:to-purple-600'
                        : 'rounded-bl-lg bg-white text-gray-900 border border-gray-200 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700',
                ]"
                @contextmenu.prevent="openMenu($event)"
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

                <div v-if="chatType === 'group' && !message.isFromMe" class="mb-1 text-xs font-bold text-purple-600 dark:text-purple-400">
                    {{ message.user.username }}
                </div>

                <div v-if="message.media && message.media.length > 0" class="mb-2">
                    <div
                        class="grid gap-1"
                        :class="{
                            'grid-cols-1': message.media.length === 1,
                            'grid-cols-2': message.media.length === 2 || message.media.length === 4,
                            'grid-cols-3': message.media.length >= 3 && message.media.length !== 4,
                        }"
                    >
                        <template v-for="(media, idx) in message.media.slice(0, 9)" :key="media.id">
                            <div
                                v-if="media.type === 'image'"
                                @click="openMediaViewer(idx)"
                                class="relative cursor-pointer overflow-hidden rounded-lg group"
                                :class="{
                                    'aspect-square': message.media.length > 1,
                                    'max-h-96 w-full': message.media.length === 1,
                                }"
                            >
                                <img
                                    :src="media.path"
                                    :alt="media.meta.original_name || 'Image'"
                                    class="w-full h-full object-cover transition group-hover:brightness-90"
                                />
                                <div v-if="idx === 8 && message.media.length > 9" class="absolute inset-0 bg-black/60 flex items-center justify-center text-white text-2xl font-bold">
                                    +{{ message.media.length - 9 }}
                                </div>
                            </div>

                            <div
                                v-else-if="media.type === 'video'"
                                @click="openMediaViewer(idx)"
                                class="relative cursor-pointer overflow-hidden rounded-lg group"
                                :class="{
                                    'aspect-video': true,
                                }"
                            >
                                <video
                                    :src="media.path"
                                    class="w-full h-full object-cover"
                                    preload="metadata"
                                />
                                <div class="absolute inset-0 bg-black/40 group-hover:bg-black/30 transition flex items-center justify-center">
                                    <div class="w-16 h-16 rounded-full bg-white/90 flex items-center justify-center shadow-lg">
                                        <svg class="w-8 h-8 text-gray-800 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-else
                                class="flex items-center gap-2 p-3 rounded-lg bg-gray-100/50 dark:bg-gray-700/50 border border-gray-200/50 dark:border-gray-600/50"
                            >
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium truncate" :class="message.isFromMe ? 'text-white' : 'text-gray-900 dark:text-gray-100'">
                                        {{ media.meta.original_name }}
                                    </div>
                                    <div class="text-xs" :class="message.isFromMe ? 'text-gray-200/80' : 'text-gray-500 dark:text-gray-400'">
                                        {{ formatFileSize(media.meta.size) }}
                                    </div>
                                </div>
                                <button
                                    @click="downloadFile(media)"
                                    class="flex-shrink-0 p-2 rounded-full transition"
                                    :class="message.isFromMe ? 'hover:bg-white/20 text-white' : 'hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300'"
                                >
                                    <Download :size="18" />
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div v-if="message.body" class="pr-3 pb-1 text-base leading-relaxed">
                    {{ message.body }}
                </div>

                <div class="flex flex-col space-y-1 text-xs" :class="message.isFromMe ? 'items-end' : 'items-start'">
                    <div class="flex items-center gap-1.5" :class="message.isFromMe ? 'text-gray-200' : 'text-gray-500 dark:text-gray-400'">
                        <span>{{ formatTime(message.created_at) }}</span>
                        <span v-if="message.isFromMe">
                            <CheckCheck v-if="message.status === 'seen'" class="h-4 w-4 text-cyan-300" />
                            <Check v-else-if="message.status === 'delivered'" class="h-4 w-4" />
                        </span>
                    </div>
                    <div
                        v-if="message.edited_at"
                        class="italic"
                        :class="message.isFromMe ? 'text-gray-200/90' : 'text-gray-500/90 dark:text-gray-400/90'"
                    >
                        edited at {{ formatTime(message.edited_at) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <Teleport to="body">
        <MediaViewer
            v-if="viewerOpen && message.media && message.media.length > 0"
            :media="message.media"
            :initial-index="viewerIndex"
            @close="viewerOpen = false"
        />
    </Teleport>
</template>

<style scoped>
.message-wrapper {
    --highlight-color: rgba(59, 130, 246, 0.36);
}

.dark .message-wrapper {
    --highlight-color: rgba(168, 85, 247, 0.36);
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
