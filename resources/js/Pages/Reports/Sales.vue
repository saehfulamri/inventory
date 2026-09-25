<script setup>
import { computed, inject, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VBadge from '../../Components/ui/VBadge.vue';
import VButton from '../../Components/ui/VButton.vue';
import VPagination from '../../Components/ui/VPagination.vue';
import VTable from '../../Components/ui/VTable.vue';
import { formatCurrency, formatDate, formatNumber } from '../../utils/format';

const props = defineProps({
    sales: { type: Object, required: true },
    summary: { type: Object, default: () => ({ total: 0, count: 0 }) },
    filters: { type: Object, default: () => ({}) },
});

const route = inject('route');

const filters = ref({
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
});

const statusLabels = {
    draft: 'Draft',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
};

const statusVariants = {
    draft: 'warning',
    completed: 'success',
    cancelled: 'danger',
};

const paymentMethodLabels = {
    cash: 'Tunai',
    transfer: 'Transfer',
    qris: 'QRIS',
    card: 'Kartu',
};

const exportParams = computed(() => {
    const params = {};

    if (filters.value.date_from) {
        params.date_from = filters.value.date_from;
    }
    if (filters.value.date_to) {
        params.date_to = filters.value.date_to;
    }

    return params;
});

const columns = [
    { key: 'sale_number', label: 'No. Penjualan' },
    { key: 'sale_date', label: 'Tanggal' },
    { key: 'user', label: 'Kasir' },
    { key: 'payment_method', label: 'Metode' },
    { key: 'grand_total', label: 'Total' },
    { key: 'status', label: 'Status' },
];

function applyFilters() {
    const params = {};

    if (filters.value.date_from) {
        params.date_from = filters.value.date_from;
    }
    if (filters.value.date_to) {
        params.date_to = filters.value.date_to;
    }

    router.get(route('reports.sales', params), {}, { preserveState: true, preserveScroll: true, replace: true });
}
</script>

<template>
    <Head title="Laporan Penjualan" />

    <AppLayout>
        <div class="page-header">
            <h1 class="page-title">Laporan Penjualan</h1>
            <div class="row-actions">
                <VButton variant="ghost" :href="route('reports.sales.export', exportParams)">Unduh CSV</VButton>
                <VButton variant="ghost" :href="route('reports.index')">← Ringkasan Laporan</VButton>
            </div>
        </div>

        <div class="summary-grid" aria-label="Ringkasan penjualan">
            <div class="summary-card">
                <span class="summary-card__label">Total Penjualan</span>
                <span class="summary-card__value">{{ formatCurrency(summary.total ?? 0) }}</span>
            </div>
            <div class="summary-card">
                <span class="summary-card__label">Jumlah Transaksi</span>
                <span class="summary-card__value">{{ formatNumber(summary.count ?? 0) }}</span>
            </div>
        </div>

        <form class="filters" @submit.prevent="applyFilters">
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
                <VButton variant="ghost" :href="route('reports.sales')">Reset</VButton>
            </div>
        </form>

        <VTable :columns="columns" :rows="sales.data ?? []">
            <template #sale_date="{ row }">{{ formatDate(row.sale_date) }}</template>

            <template #user="{ row }">{{ row.user?.name ?? '—' }}</template>

            <template #payment_method="{ row }">
                {{ paymentMethodLabels[row.payment_method] ?? row.payment_method }}
            </template>

            <template #grand_total="{ row }">{{ formatCurrency(row.grand_total) }}</template>

            <template #status="{ row }">
                <VBadge :variant="statusVariants[row.status] ?? 'warning'">
                    {{ statusLabels[row.status] ?? row.status }}
                </VBadge>
            </template>

            <template #empty>
                <p>Belum ada penjualan pada periode ini.</p>
            </template>
        </VTable>

        <VPagination :links="sales.links ?? []" />
    </AppLayout>
</template>