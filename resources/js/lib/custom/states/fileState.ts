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
    if (fileState.files.length >= 10) {
        mainPopupState.show('Maximum 10 files allowed', 'error')
        return
    }

    const remainingSlots = 10 - fileState.files.length
    const filesToAdd = Array.from(newFiles).slice(0, remainingSlots)

    filesToAdd.forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            mainPopupState.show('File size exceeds 10MB limit: ' + file.name, 'error')
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

    if (newFiles.length > remainingSlots) {
        mainPopupState.show(`Only ${remainingSlots} files added (max 10 total)`, 'warning')
    }
}

export function removeFile(index: number) {
    URL.revokeObjectURL(fileState.files[index].preview)
    fileState.files.splice(index, 1)
}

export function clearFiles() {
    fileState.files.forEach(f => URL.revokeObjectURL(f.preview))
    fileState.files = []
}
