<script setup>
defineProps({
    variant: { type: String, default: 'muted' }, // success | warning | danger | muted
});
</script>

<template>
    <span class="badge" :class="`badge--${variant}`"><slot /></span>
</template>