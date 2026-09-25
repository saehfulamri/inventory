<script setup>
import { computed, inject, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VButton from '../../Components/ui/VButton.vue';
import VPagination from '../../Components/ui/VPagination.vue';
import VSelect from '../../Components/ui/VSelect.vue';
import VTable from '../../Components/ui/VTable.vue';
import { formatDateTime, formatQuantity } from '../../utils/format';

const props = defineProps({
    movements: { type: Object, required: true },
    movementTypes: { type: Array, default: () => [] }, // [{ value, label }]
    products: { type: Array, default: () => [] }, // [{ id, name }]
    filters: { type: Object, default: () => ({}) },
});

const route = inject('route');

const filters = ref({
    product_id: props.filters.product_id !== undefined && props.filters.product_id !== null
        ? String(props.filters.product_id)
        : '',
    movement_type: props.filters.movement_type ?? '',
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
});

const productOptions = computed(() => [
    { value: '', label: 'Semua' },
    ...props.products.map((product) => ({ value: String(product.id), label: product.name })),
]);

const typeOptions = computed(() => [
    { value: '', label: 'Semua' },
    ...props.movementTypes,
]);

const typeLabels = computed(() => {
    const map = {};

    for (const type of props.movementTypes) {
        map[type.value] = type.label;
    }

    return map;
});

const columns = [
    { key: 'created_at', label: 'Waktu' },
    { key: 'product', label: 'Produk' },
    { key: 'movement_type', label: 'Tipe' },
    { key: 'delta', label: 'Perubahan' },
    { key: 'stock', label: 'Stok' },
    { key: 'user', label: 'Oleh' },
];

function delta(row) {
    return Number(row.quantity);
}

function applyFilters() {
    const params = {};

    if (filters.value.product_id !== '') {
        params.product_id = filters.value.product_id;
    }
    if (filters.value.movement_type) {
        params.movement_type = filters.value.movement_type;
    }
    if (filters.value.date_from) {
        params.date_from = filters.value.date_from;
    }
    if (filters.value.date_to) {
        params.date_to = filters.value.date_to;
    }

    router.get(route('reports.movements', params), {}, { preserveState: true, preserveScroll: true, replace: true });
}
</script>

<template>
    <Head title="Laporan Pergerakan Stok" />

    <AppLayout>
        <div class="page-header">
            <h1 class="page-title">Laporan Pergerakan Stok</h1>
            <VButton variant="ghost" :href="route('reports.index')">← Ringkasan Laporan</VButton>
        </div>

        <form class="filters" @submit.prevent="applyFilters">
            <VSelect
                id="product_id"
                label="Produk"
                v-model="filters.product_id"
                :options="productOptions"
            />

            <VSelect
                id="movement_type"
                label="Tipe"
                v-model="filters.movement_type"
                :options="typeOptions"
            />

            <div class="form__group">
                <label for="date_from">Dari Tanggal</label>
                <input id="date_from" v-model="filters.date_from" type="date" />
            </div>

            <div class="form__group">
                <label for="date_to">Sampai Tanggal</label>
                <input id="date_to" v-model="filters.date_to" type="date" />
            </div>

            <div class="form__actions">
                <VButton type="submit" variant="ghost">Terapkan Filter</VButton>
                <VButton variant="ghost" :href="route('reports.movements')">Reset</VButton>
            </div>
        </form>

        <VTable :columns="columns" :rows="movements.data ?? []">
            <template #created_at="{ row }">{{ formatDateTime(row.created_at) }}</template>

            <template #product="{ row }">{{ row.product?.name ?? '—' }}</template>

            <template #movement_type="{ row }">
                <span class="badge badge--ghost">{{ typeLabels[row.movement_type] ?? row.movement_type }}</span>
            </template>

            <template #delta="{ row }">
                <span class="badge" :class="delta(row) >= 0 ? 'badge--success' : 'badge--danger'">
                    {{ delta(row) >= 0 ? '+' : '−' }}{{ formatQuantity(Math.abs(delta(row))) }}
                </span>
            </template>

            <template #stock="{ row }">
                {{ row.stock_before }} → {{ row.stock_after }}
            </template>

            <template #user="{ row }">{{ row.user?.name ?? '—' }}</template>

            <template #empty>
                <p>Belum ada pergerakan stok pada filter ini.</p>
            </template>
        </VTable>

        <VPagination :links="movements.links ?? []" />
    </AppLayout>
</template>