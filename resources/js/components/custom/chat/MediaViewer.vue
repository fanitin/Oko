<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { X, ChevronLeft, ChevronRight, ZoomIn, ZoomOut, Download } from 'lucide-vue-next'

interface MediaItem {
    id: number
    type: 'image' | 'video' | 'file'
    path: string
    meta: {
        original_name?: string
        size?: number
        mime_type?: string
    }
}

const props = defineProps<{
    media: MediaItem[]
    initialIndex: number
}>()

const emit = defineEmits<{
    close: []
}>()

const currentIndex = ref(props.initialIndex)
const zoom = ref(1)
const isDragging = ref(false)
const startX = ref(0)
const startY = ref(0)
const translateX = ref(0)
const translateY = ref(0)

const currentMedia = computed(() => props.media[currentIndex.value])
const isFirstItem = computed(() => currentIndex.value === 0)
const isLastItem = computed(() => currentIndex.value === props.media.length - 1)

const goToPrevious = () => {
    if (!isFirstItem.value) {
        currentIndex.value--
        resetZoom()
    }
}

const goToNext = () => {
    if (!isLastItem.value) {
        currentIndex.value++
        resetZoom()
    }
}

const zoomIn = () => {
    if (zoom.value < 3) {
        zoom.value += 0.5
    }
}

const zoomOut = () => {
    if (zoom.value > 0.5) {
        zoom.value -= 0.5
    }
}

const resetZoom = () => {
    zoom.value = 1
    translateX.value = 0
    translateY.value = 0
}

const handleMouseDown = (e: MouseEvent) => {
    if (currentMedia.value.type === 'image' && zoom.value > 1) {
        isDragging.value = true
        startX.value = e.clientX - translateX.value
        startY.value = e.clientY - translateY.value
    }
}

const handleMouseMove = (e: MouseEvent) => {
    if (isDragging.value) {
        translateX.value = e.clientX - startX.value
        translateY.value = e.clientY - startY.value
    }
}

const handleMouseUp = () => {
    isDragging.value = false
}

const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        emit('close')
    } else if (e.key === 'ArrowLeft') {
        goToPrevious()
    } else if (e.key === 'ArrowRight') {
        goToNext()
    }
}

const downloadFile = () => {
    const link = document.createElement('a')
    link.href = currentMedia.value.path
    link.download = currentMedia.value.meta.original_name || 'download'
    link.click()
}

const formatFileSize = (bytes?: number) => {
    if (!bytes) return 'Unknown size'
    const mb = bytes / (1024 * 1024)
    return mb.toFixed(2) + ' MB'
}

onMounted(() => {
    document.addEventListener('keydown', handleKeyDown)
    document.addEventListener('mousemove', handleMouseMove)
    document.addEventListener('mouseup', handleMouseUp)
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown)
    document.removeEventListener('mousemove', handleMouseMove)
    document.removeEventListener('mouseup', handleMouseUp)
})
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/95">
        <div class="absolute top-0 left-0 right-0 z-10 flex items-center justify-between p-4 bg-gradient-to-b from-black/80 to-transparent">
            <div class="text-white">
                <div class="text-sm font-medium">{{ currentMedia.meta.original_name }}</div>
                <div class="text-xs text-gray-400">{{ currentIndex + 1 }} / {{ media.length }}</div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    v-if="currentMedia.type === 'image'"
                    @click="zoomOut"
                    class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition text-white"
                    :disabled="zoom <= 0.5"
                >
                    <ZoomOut :size="20" />
                </button>
                <button
                    v-if="currentMedia.type === 'image'"
                    @click="zoomIn"
                    class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition text-white"
                    :disabled="zoom >= 3"
                >
                    <ZoomIn :size="20" />
                </button>
                <button
                    @click="downloadFile"
                    class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition text-white"
                >
                    <Download :size="20" />
                </button>
                <button
                    @click="emit('close')"
                    class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition text-white"
                >
                    <X :size="24" />
                </button>
            </div>
        </div>

        <button
            v-if="!isFirstItem"
            @click="goToPrevious"
            class="absolute left-4 z-10 p-3 rounded-full bg-white/10 hover:bg-white/20 transition text-white"
        >
            <ChevronLeft :size="32" />
        </button>

        <button
            v-if="!isLastItem"
            @click="goToNext"
            class="absolute right-4 z-10 p-3 rounded-full bg-white/10 hover:bg-white/20 transition text-white"
        >
            <ChevronRight :size="32" />
        </button>

        <div class="w-full h-full flex items-center justify-center p-16">
            <img
                v-if="currentMedia.type === 'image'"
                :src="currentMedia.path"
                :alt="currentMedia.meta.original_name || 'Image'"
                :style="{
                    transform: `scale(${zoom}) translate(${translateX / zoom}px, ${translateY / zoom}px)`,
                    cursor: zoom > 1 ? (isDragging ? 'grabbing' : 'grab') : 'default',
                    transition: isDragging ? 'none' : 'transform 0.2s ease-out'
                }"
                @mousedown="handleMouseDown"
                class="max-w-full max-h-full object-contain select-none"
                draggable="false"
            />

            <video
                v-else-if="currentMedia.type === 'video'"
                :src="currentMedia.path"
                controls
                class="max-w-full max-h-full"
            />

            <div
                v-else
                class="bg-gray-900 rounded-xl p-8 text-white max-w-md"
            >
                <div class="text-center">
                    <div class="mb-4 text-gray-400">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="text-lg font-semibold mb-2">{{ currentMedia.meta.original_name }}</div>
                    <div class="text-sm text-gray-400 mb-6">{{ formatFileSize(currentMedia.meta.size) }}</div>
                    <button
                        @click="downloadFile"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg transition"
                    >
                        Download File
                    </button>
                </div>
            </div>
        </div>

        <div
            v-if="media.length > 1"
            class="absolute bottom-0 left-0 right-0 z-10 flex items-center justify-center gap-2 p-4 bg-gradient-to-t from-black/80 to-transparent overflow-x-auto"
        >
            <button
                v-for="(item, idx) in media"
                :key="item.id"
                @click="currentIndex = idx; resetZoom()"
                class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition"
                :class="[
                    idx === currentIndex
                        ? 'border-blue-500 dark:border-purple-500'
                        : 'border-transparent hover:border-gray-400'
                ]"
            >
                <img
                    v-if="item.type === 'image'"
                    :src="item.path"
                    :alt="item.meta.original_name || 'Thumbnail'"
                    class="w-full h-full object-cover"
                />
                <div
                    v-else
                    class="w-full h-full bg-gray-700 flex items-center justify-center text-white text-xs"
                >
                    {{ item.type === 'video' ? '📹' : '📄' }}
                </div>
            </button>
        </div>
    </div>
</template>
