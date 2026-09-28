<script setup>
defineProps({
    /** Badge visual variant: success | warning | danger | muted */
    variant: { type: String, default: 'muted' },
    /** Optional accessible label (screen‑reader only) when the badge conveys status */
    ariaLabel: { type: String, default: null },
});
</script>

<template>
    <span
        class="badge"
        :class="`badge--${variant}`"
        :aria-label="ariaLabel ?? undefined"
    ><slot /></span>
</template>