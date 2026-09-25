<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import SupplierForm from '../../Components/Suppliers/SupplierForm.vue';

defineProps({
    supplier: { type: Object, required: true },
});
</script>

<template>
    <Head title="Edit Supplier" />

    <AppLayout>
        <h1 class="page-title">Edit Supplier</h1>

        <p class="muted">{{ supplier.code || supplier.name }}</p>

        <SupplierForm :supplier="supplier" />
    </AppLayout>
</template>