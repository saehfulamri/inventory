<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import ProductForm from '../../Components/Products/ProductForm.vue';

defineProps({
    product: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    units: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Edit Produk" />

    <AppLayout>
        <h1 class="page-title">Edit Produk</h1>

        <p class="muted">{{ product.sku }} — {{ product.name }}</p>

        <ProductForm :product="product" :categories="categories" :units="units" />
    </AppLayout>
</template>