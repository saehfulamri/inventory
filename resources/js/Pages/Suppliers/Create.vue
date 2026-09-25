<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import SupplierForm from '../../Components/Suppliers/SupplierForm.vue';
</script>

<template>
    <Head title="Tambah Supplier" />

    <AppLayout>
        <h1 class="page-title">Tambah Supplier</h1>

        <SupplierForm />
    </AppLayout>
</template>