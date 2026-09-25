<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, default: () => [] }, // [{ url, label, active }]
});
</script>

<template>
    <nav v-if="links.length" aria-label="Navigasi halaman">
        <ul class="pagination">
            <li
                v-for="(link, index) in links"
                :key="index"
                :class="{ disabled: !link.url, active: link.active }"
            >
                <Link v-if="link.url" :href="link.url" preserve-scroll v-html="link.label" />
                <span v-else v-html="link.label" />
            </li>
        </ul>
    </nav>
</template>