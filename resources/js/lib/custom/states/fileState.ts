import { reactive } from 'vue'
import { mainPopupState } from '@/lib/custom/states/mainPopupState';

export interface UploadFile {
    file: File
    preview: string
    type: 'image' | 'video' | 'file'
}

interface FileState {
    files: UploadFile[]
}

export const fileState = reactive<FileState>({
    files: [],
})

export function addFiles(newFiles: File[]) {
    newFiles.forEach(file => {
        if (file.size > 3 * 1024 * 1024) {
            mainPopupState.show('File size exceeds 3MB limit: ' + file.name, 'error')
            return
        }

        let type: 'image' | 'video' | 'file' = 'file'
        if (file.type.startsWith('image/')) type = 'image'
        else if (file.type.startsWith('video/')) type = 'video'

        fileState.files.push({
            file,
            preview: URL.createObjectURL(file),
            type,
        })
    })
}

export function removeFile(index: number) {
    URL.revokeObjectURL(fileState.files[index].preview)
    fileState.files.splice(index, 1)
}

export function clearFiles() {
    fileState.files.forEach(f => URL.revokeObjectURL(f.preview))
    fileState.files = []
}
