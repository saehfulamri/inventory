<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import UnitForm from '../../Components/Units/UnitForm.vue';
</script>

<template>
    <Head title="Tambah Satuan" />

    <AppLayout>
        <h1 class="page-title">Tambah Satuan</h1>

        <UnitForm />
    </AppLayout>
</template>
