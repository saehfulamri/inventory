<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import ProductForm from '../../Components/Products/ProductForm.vue';

defineProps({
    categories: { type: Array, default: () => [] },
    units: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Tambah Produk" />

    <AppLayout>
        <h1 class="page-title">Tambah Produk</h1>

        <ProductForm :categories="categories" :units="units" />
    </AppLayout>
</template>