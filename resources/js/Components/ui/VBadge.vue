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
        :class="[
            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
            {
                'bg-primary text-white': variant === 'primary',
                'bg-success text-white': variant === 'success',
                'bg-warning text-white': variant === 'warning',
                'bg-danger text-white': variant === 'danger',
                'bg-muted text-white': variant === 'muted',
            }
        ]"
        :aria-label="ariaLabel ?? undefined"
    ><slot /></span>
</template>