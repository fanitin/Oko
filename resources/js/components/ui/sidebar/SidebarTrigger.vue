<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { PanelLeft } from 'lucide-vue-next'
import { useSidebar } from './utils'

const { toggleSidebar, state } = useSidebar()
</script>

<template>
    <Button
        variant="ghost"
        size="icon"
        @click="toggleSidebar"
        class="transition-all duration-200"
    >
        <PanelLeft class="h-5 w-5" />
        <span class="sr-only">Toggle Sidebar</span>
    </Button>
</template>
