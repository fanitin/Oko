<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    class?: HTMLAttributes['class'];
}

defineProps<Props>();

const imagePath = '/images/logo/oko.png';
</script>

<template>
    <img
        :src="imagePath"
        alt="Logo"
        :class="['object-contain', $props.class]"
    />
</template>
