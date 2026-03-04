<script setup lang="ts">
import { fileState, removeFile } from '@/lib/custom/states/fileState'
import FilePreviewBar from './FilePreviewBar.vue'
</script>

<template>
    <FilePreviewBar :files="fileState.files" @remove="removeFile" />
</template>
