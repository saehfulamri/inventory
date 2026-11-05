<script setup>
defineProps({
    name: { type: String, required: true },
});

// 24x24 stroke icons (Feather-style), decorative: the parent provides the accessible name.
const paths = {
    dashboard: ['M3 3h7v9H3z', 'M14 3h7v5h-7z', 'M14 12h7v9h-7z', 'M3 16h7v5H3z'],
    sales: ['M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z', 'M3 6h18', 'M16 10a4 4 0 0 1-8 0'],
    products: ['M21 8 12 3 3 8v8l9 5 9-5z', 'M3 8l9 5 9-5', 'M12 13v8'],
    inventory: ['M3 3v18h18', 'M7 15v3', 'M12 9v9', 'M17 5v13'],
    purchases: ['M1 3h4l2.7 12.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 7H6', 'M10 21h.01', 'M19 21h.01'],
    suppliers: ['M1 3h15v13H1z', 'M16 8h4l3 3v5h-7z', 'M5.5 18.5a2.5 2.5 0 1 0 0 .01', 'M18.5 18.5a2.5 2.5 0 1 0 0 .01'],
    categories: ['M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8z', 'M7 7h.01'],
    units: ['M21.3 8.7 8.7 21.3a1 1 0 0 1-1.4 0L2.7 16.7a1 1 0 0 1 0-1.4L15.3 2.7a1 1 0 0 1 1.4 0l4.6 4.6a1 1 0 0 1 0 1.4z', 'M7.5 10.5 10 13', 'M10.5 7.5 13 10', 'M13.5 4.5 16 7'],
    reports: ['M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z', 'M14 2v6h6', 'M8 13h8', 'M8 17h8'],
    users: ['M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2', 'M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z', 'M23 21v-2a4 4 0 0 0-3-3.9', 'M16 3.1a4 4 0 0 1 0 7.8'],
    more: ['M5 12h.01', 'M12 12h.01', 'M19 12h.01'],
};
</script>

<template>
    <svg
        class="nav-icon"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        focusable="false"
    >
        <path v-for="d in paths[name] ?? []" :key="d" :d="d" />
    </svg>
</template>
