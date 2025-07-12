<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();

const imagePath = "/images/logo/oko.jpg"
</script>

<template>
    <img :src="imagePath" class="" alt="Logo">
</template>
