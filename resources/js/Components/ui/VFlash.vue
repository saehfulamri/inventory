<script setup>
defineProps({
    type: { type: String, default: 'success' }, // success | error
});
</script>

<template>
    <div class="flash" :class="`flash--${type}`" :role="type === 'error' ? 'alert' : 'status'">
        <slot />
    </div>
</template>