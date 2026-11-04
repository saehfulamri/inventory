<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import UnitForm from '../../Components/Units/UnitForm.vue';

defineProps({
    unit: { type: Object, required: true },
});
</script>

<template>
    <Head title="Edit Satuan" />

    <AppLayout>
        <h1 class="page-title">Edit Satuan</h1>

        <p class="muted">{{ unit.name }} ({{ unit.symbol }})</p>

        <UnitForm :unit="unit" />
    </AppLayout>
</template>
