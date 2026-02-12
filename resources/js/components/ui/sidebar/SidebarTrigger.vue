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
        :class="[
            'transition-all duration-200',
            state === 'collapsed' ? 'h-12 w-12' : 'h-10 w-10'
        ]"
    >
        <PanelLeft :class="state === 'collapsed' ? 'h-6 w-6' : 'h-5 w-5'" />
        <span class="sr-only">Toggle Sidebar</span>
    </Button>
</template>
