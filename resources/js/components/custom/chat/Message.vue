<script setup lang="ts">
import { Check, CheckCheck, Download } from 'lucide-vue-next';
import { messageContextMenu } from '@/lib/custom/messageContextMenu';
import { computed, onUnmounted, ref } from 'vue';
import MediaViewer from './MediaViewer.vue';

interface MessageMedia {
    id: number;
    type: 'image' | 'video' | 'file';
    path: string;
    meta: {
        original_name?: string;
        size?: number;
        mime_type?: string;
    };
}

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
        media?: MessageMedia[];
    };
    chatType: 'self' | 'private' | 'group';
}>();

const emit = defineEmits(['fetch-and-scroll']);
const elRef = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

const viewerOpen = ref(false);
const viewerIndex = ref(0);

const mediaItems = computed(() => props.message.media ?? []);
const visualMedia = computed(() =>
    mediaItems.value
        .map((media, index) => ({ media, index }))
        .filter((item) => item.media.type === 'image' || item.media.type === 'video'),
);
const fileMedia = computed(() => mediaItems.value.filter((item) => item.type === 'file'));
const hasText = computed(() => Boolean(props.message.body?.trim()));
const isMediaOnly = computed(() => visualMedia.value.length > 0 && !hasText.value && fileMedia.value.length === 0);

const mediaGridClass = computed(() => {
    const count = visualMedia.value.length;

    if (count <= 1) return 'grid-cols-1';
    if (count <= 4) return 'grid-cols-2';
    if (count <= 9) return 'grid-cols-3';
    if (count === 10) return 'grid-cols-4';
    return 'grid-cols-3';
});

const mediaBoxClass = computed(() => {
    if (visualMedia.value.length <= 1) return 'max-w-[17rem] sm:max-w-[20rem]';
    if (visualMedia.value.length <= 4) return 'max-w-[17.5rem] sm:max-w-[21rem]';
    return 'max-w-[18rem] sm:max-w-[22rem]';
});

const openMediaViewer = (index: number) => {
    viewerIndex.value = index;
    viewerOpen.value = true;
};

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
    if (!bytes) return 'Unknown';
    const mb = bytes / (1024 * 1024);
    if (mb < 1) return (bytes / 1024).toFixed(1) + ' KB';
    return mb.toFixed(2) + ' MB';
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

const downloadFile = (media: MessageMedia) => {
    const link = document.createElement('a');
    link.href = media.path;
    link.download = media.meta.original_name || 'download';
    link.click();
};
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
                class="message-bubble relative max-w-[74%] rounded-2xl break-words shadow-sm ring-1 md:max-w-[62%]"
                :class="[
                    message.isFromMe
                        ? 'rounded-br-md bg-gradient-to-br from-blue-500 to-blue-400 text-white ring-blue-300/40 dark:from-purple-700 dark:to-purple-600 dark:ring-purple-400/20'
                        : 'rounded-bl-md bg-white text-gray-900 ring-gray-200 dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-700',
                    isMediaOnly ? 'px-1.5 py-1.5' : 'px-3.5 py-2.5',
                ]"
                @contextmenu.prevent="openMenu($event)"
            >
                <div
                    v-if="message.reply_to"
                    class="mb-2 cursor-pointer rounded-lg border-l-4 border-blue-400 bg-blue-50/90 py-1.5 pr-3 pl-2 text-sm dark:border-purple-500 dark:bg-purple-900/20"
                    @click="scrollToReply(message.reply_to.id)"
                >
                    <div class="font-semibold text-blue-700 dark:text-purple-300">{{ message.reply_to.user.username }}</div>
                    <div class="max-w-[260px] truncate text-gray-900 dark:text-gray-300">
                        {{ message.reply_to.body }}
                    </div>
                </div>

                <div
                    v-if="chatType === 'group' && !message.isFromMe"
                    class="mb-1 text-xs font-semibold tracking-wide text-purple-600/90 dark:text-purple-400"
                >
                    {{ message.user.username }}
                </div>

                <div v-if="visualMedia.length > 0" :class="isMediaOnly ? 'mb-0' : 'mb-2'">
                    <div class="grid gap-1 overflow-hidden rounded-xl" :class="[mediaGridClass, mediaBoxClass]">
                        <template v-for="(item, idx) in visualMedia.slice(0, 10)" :key="item.media.id">
                            <button
                                type="button"
                                @click="openMediaViewer(item.index)"
                                class="group relative cursor-pointer overflow-hidden bg-black/10 text-left"
                                :class="[
                                    visualMedia.length === 1 ? 'aspect-[4/3] max-h-[20rem] w-full rounded-xl' : 'aspect-square',
                                ]"
                            >
                                <img
                                    v-if="item.media.type === 'image'"
                                    :src="item.media.path"
                                    :alt="item.media.meta.original_name || 'Image'"
                                    class="block h-full w-full object-cover transition duration-200 group-hover:scale-[1.02]"
                                />

                                <template v-else>
                                    <video :src="item.media.path" class="block h-full w-full object-cover" preload="metadata" />
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 transition group-hover:bg-black/20">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/90 shadow-lg">
                                            <svg class="ml-0.5 h-5 w-5 text-gray-800" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                </template>

                                <div
                                    v-if="idx === 9 && visualMedia.length > 10"
                                    class="absolute inset-0 flex items-center justify-center bg-black/65 text-2xl font-bold text-white"
                                >
                                    +{{ visualMedia.length - 10 }}
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <div v-if="fileMedia.length > 0" class="mb-2 space-y-1.5">
                    <div
                        v-for="media in fileMedia"
                        :key="media.id"
                        class="flex items-center gap-2 rounded-lg border p-2.5"
                        :class="message.isFromMe ? 'border-white/20 bg-white/10' : 'border-gray-200 bg-gray-100/70 dark:border-gray-600/60 dark:bg-gray-700/40'"
                    >
                        <div class="flex-shrink-0">
                            <svg class="h-7 w-7" :class="message.isFromMe ? 'text-white/80' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-medium" :class="message.isFromMe ? 'text-white' : 'text-gray-900 dark:text-gray-100'">
                                {{ media.meta.original_name }}
                            </div>
                            <div class="text-xs" :class="message.isFromMe ? 'text-white/75' : 'text-gray-500 dark:text-gray-400'">
                                {{ formatFileSize(media.meta.size) }}
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="downloadFile(media)"
                            class="flex-shrink-0 rounded-full p-2 transition"
                            :class="message.isFromMe ? 'text-white hover:bg-white/20' : 'text-gray-600 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-600'"
                        >
                            <Download :size="18" />
                        </button>
                    </div>
                </div>

                <div v-if="message.body" class="pb-0.5 text-[15px] leading-relaxed">
                    {{ message.body }}
                </div>

                <div
                    v-if="isMediaOnly"
                    class="absolute right-2 bottom-2 flex items-center gap-1 rounded-md bg-black/40 px-1.5 py-0.5 text-[11px] text-white backdrop-blur-sm"
                >
                    <span>{{ formatTime(message.created_at) }}</span>
                    <span v-if="message.edited_at" class="italic opacity-90">edited</span>
                    <span v-if="message.isFromMe" class="flex items-center">
                        <CheckCheck v-if="message.status === 'seen'" class="h-3.5 w-3.5 text-cyan-200" />
                        <Check v-else-if="message.status === 'delivered'" class="h-3.5 w-3.5" />
                    </span>
                </div>

                <div
                    v-else
                    class="mt-1 flex items-center gap-1.5 text-[11px]"
                    :class="message.isFromMe ? 'justify-end text-blue-100/95' : 'justify-start text-gray-500 dark:text-gray-400'"
                >
                    <span>{{ formatTime(message.created_at) }}</span>
                    <span v-if="message.edited_at" class="italic opacity-90">edited</span>
                    <span v-if="message.isFromMe" class="flex items-center">
                        <CheckCheck v-if="message.status === 'seen'" class="h-3.5 w-3.5 text-cyan-200" />
                        <Check v-else-if="message.status === 'delivered'" class="h-3.5 w-3.5" />
                    </span>
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
