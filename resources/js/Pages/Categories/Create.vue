<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import CategoryForm from '../../Components/Categories/CategoryForm.vue';
</script>

<template>
    <Head title="Tambah Kategori" />

    <AppLayout>
        <h1 class="page-title">Tambah Kategori</h1>

        <CategoryForm />
    </AppLayout>
</template>
