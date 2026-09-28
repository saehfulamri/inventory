<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, default: null },
    type: { type: String, default: 'button' },
    disabled: { type: Boolean, default: false },
});

const classes = computed(() => {
    const base = [
        'bg-primary',
        'text-on-primary',
        'rounded-pill',
        'py-2.5',
        'px-5',
        'focus:outline-none',
        'focus-visible:ring-2',
        'focus-visible:ring-primary-focus',
        'focus-visible:ring-offset-2',
        'transform',
        'transition',
        'duration-150',
        'ease-in-out',
        'hover:scale-95',
        'active:scale-95',
        'disabled:opacity-50',
        'disabled:pointer-events-none',
    ];
    return base.join(' ');
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
