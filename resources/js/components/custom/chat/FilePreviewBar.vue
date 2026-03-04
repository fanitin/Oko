<script setup lang="ts">
import { ref } from 'vue'
import { X, ZoomIn } from 'lucide-vue-next'
import MediaViewer from './MediaViewer.vue'

interface FilePreview {
    file: File
    preview: string
    type: 'image' | 'video' | 'file'
}

const props = defineProps<{
    files: FilePreview[]
}>()

const emit = defineEmits<{
    remove: [index: number]
}>()

const viewerOpen = ref(false)
const viewerIndex = ref(0)

const viewerMedia = ref<any[]>([])

const openViewer = (index: number) => {
    viewerMedia.value = props.files.map((f, idx) => ({
        id: idx,
        type: f.type,
        path: f.preview,
        meta: {
            original_name: f.file.name,
            size: f.file.size,
            mime_type: f.file.type,
        }
    }))
    viewerIndex.value = index
    viewerOpen.value = true
}
</script>

<template>
    <div>
        <div
            v-if="files.length"
            class="mb-3 flex gap-3 overflow-x-auto rounded-xl border bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900 shadow-md"
        >
            <div
                v-for="(f, i) in files"
                :key="i"
                class="relative h-24 w-32 flex-shrink-0 overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-center group"
            >
                <div
                    v-if="f.type === 'image'"
                    class="relative w-full h-full cursor-pointer"
                    @click="openViewer(i)"
                >
                    <img
                        :src="f.preview"
                        :alt="f.file.name"
                        class="h-full w-full object-cover rounded-lg"
                    />
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition flex items-center justify-center">
                        <ZoomIn class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition" />
                    </div>
                </div>

                <div
                    v-else-if="f.type === 'video'"
                    class="relative w-full h-full cursor-pointer"
                    @click="openViewer(i)"
                >
                    <video
                        :src="f.preview"
                        muted
                        class="h-full w-full object-cover rounded-lg"
                    />
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition flex items-center justify-center">
                        <div class="text-white opacity-0 group-hover:opacity-100 transition">▶️</div>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center w-full h-full p-2 bg-gray-100 dark:bg-gray-700 rounded-lg"
                >
                    <svg class="w-8 h-8 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs text-gray-600 dark:text-gray-300 truncate w-full text-center">{{ f.file.name }}</span>
                </div>

                <button
                    @click="emit('remove', i)"
                    class="absolute top-1 right-1 flex h-7 w-7 items-center justify-center rounded-full bg-black/70 text-white hover:bg-black transition shadow-lg"
                >
                    <X :size="16" />
                </button>
            </div>
        </div>

        <Teleport to="body">
            <MediaViewer
                v-if="viewerOpen"
                :media="viewerMedia"
                :initial-index="viewerIndex"
                @close="viewerOpen = false"
            />
        </Teleport>
    </div>
</template>
