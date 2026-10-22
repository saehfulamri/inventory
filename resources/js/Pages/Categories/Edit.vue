<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import CategoryForm from '../../Components/Categories/CategoryForm.vue';

defineProps({
    category: { type: Object, required: true },
});
</script>

<template>
    <Head title="Edit Kategori" />

    <AppLayout>
        <h1 class="page-title">Edit Kategori</h1>

        <p class="muted">{{ category.name }}</p>

        <CategoryForm :category="category" />
    </AppLayout>
</template>
