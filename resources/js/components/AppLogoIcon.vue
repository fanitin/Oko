<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();

const imagePath = '/images/logo/oko.png';
</script>

<template>
    <img
        :src="imagePath"
        alt="Logo"
        class="h-12 w-12 rounded-md object-contain shadow-md transition-transform duration-500 ease-in-out hover:scale-125 hover:shadow-lg md:h-16 md:w-16 dark:brightness-110"
    />
</template>
