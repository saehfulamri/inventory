<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    variant: { type: String, default: 'primary' }, // primary | ghost | danger
    size: { type: String, default: null }, // null | sm
    block: { type: Boolean, default: false },
    type: { type: String, default: 'button' },
    href: { type: String, default: null },
    disabled: { type: Boolean, default: false },
});

const classes = computed(() => {
    const parts = ['btn', `btn--${props.variant}`];

    if (props.size) {
        parts.push(`btn--${props.size}`);
    }

    if (props.block) {
        parts.push('btn--block');
    }

    return parts.join(' ');
});
</script>

<template>
    <Link v-if="href" :href="href" :class="classes">
        <slot />
    </Link>
    <button v-else :type="type" :class="classes" :disabled="disabled">
        <slot />
    </button>
</template>