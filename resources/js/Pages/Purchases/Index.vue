<script setup>
import { computed, inject, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VBadge from '../../Components/ui/VBadge.vue';
import VButton from '../../Components/ui/VButton.vue';
import VPagination from '../../Components/ui/VPagination.vue';
import VSelect from '../../Components/ui/VSelect.vue';
import VTable from '../../Components/ui/VTable.vue';
import { formatCurrency, formatDate } from '../../utils/format';

const props = defineProps({
    purchases: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    suppliers: { type: Array, default: () => [] },
    statuses: { type: Array, default: () => [] }, // [{ value, label }]
});

const route = inject('route');
const page = usePage();
const can = computed(() => page.props.can ?? {});

const filters = ref({
    keyword: props.filters.keyword ?? '',
    supplier_id: props.filters.supplier_id !== undefined && props.filters.supplier_id !== null
        ? String(props.filters.supplier_id)
        : '',
    status: props.filters.status ?? '',
});

const supplierOptions = computed(() => [
    { value: '', label: 'Semua' },
    ...props.suppliers.map((supplier) => ({ value: String(supplier.id), label: supplier.name })),
]);

const statusOptions = computed(() => [
    { value: '', label: 'Semua' },
    ...props.statuses,
]);

const statusLabels = computed(() => {
    const map = {};

    for (const status of props.statuses) {
        map[status.value] = status.label;
    }

    return map;
});

const statusVariants = {
    draft: 'warning',
    completed: 'success',
    cancelled: 'danger',
};

const columns = [
    { key: 'purchase_number', label: 'No. Penerimaan' },
    { key: 'purchase_date', label: 'Tanggal' },
    { key: 'supplier', label: 'Supplier' },
    { key: 'total_amount', label: 'Total' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Aksi' },
];

function applyFilters() {
    const params = {};

    if (filters.value.keyword) {
        params.keyword = filters.value.keyword;
    }
    if (filters.value.supplier_id !== '') {
        params.supplier_id = filters.value.supplier_id;
    }
    if (filters.value.status) {
        params.status = filters.value.status;
    }

    router.get(route('purchases.index', params), {}, { preserveState: true, preserveScroll: true, replace: true });
}
</script>

<template>
    <Head title="Penerimaan" />

    <AppLayout>
        <div class="page-header">
            <h1 class="page-title">Penerimaan</h1>
            <VButton v-if="can.viewAnyPurchases" :href="route('purchases.create')">Tambah Penerimaan</VButton>
        </div>

        <form class="filters" @submit.prevent="applyFilters">
            <div class="form__group">
                <label for="keyword">Cari</label>
                <input
                    id="keyword"
                    v-model.trim="filters.keyword"
                    type="text"
                    placeholder="Nomor penerimaan"
                />
            </div>

            <VSelect
                id="supplier_id"
                label="Supplier"
                v-model="filters.supplier_id"
                :options="supplierOptions"
            />

            <VSelect
                id="status"
                label="Status"
                v-model="filters.status"
                :options="statusOptions"
            />

            <div class="form__actions">
                <VButton type="submit" variant="ghost">Terapkan Filter</VButton>
            </div>
        </form>

        <VTable :columns="columns" :rows="purchases.data ?? []">
            <template #purchase_date="{ row }">{{ formatDate(row.purchase_date) }}</template>

            <template #supplier="{ row }">{{ row.supplier?.name ?? '—' }}</template>

            <template #total_amount="{ row }">{{ formatCurrency(row.total_amount) }}</template>

            <template #status="{ row }">
                <VBadge :variant="statusVariants[row.status] ?? 'muted'">
                    {{ statusLabels[row.status] ?? row.status }}
                </VBadge>
            </template>

            <template #actions="{ row }">
                <VButton
                    variant="ghost"
                    size="sm"
                    :href="route('purchases.show', row.id)"
                >Detail</VButton>
            </template>

            <template #empty>
                <p>Belum ada penerimaan.</p>
                <p class="muted">Buat penerimaan pertama untuk mulai mencatat barang masuk.</p>
            </template>
        </VTable>

        <VPagination :links="purchases.links ?? []" />
    </AppLayout>
</template>