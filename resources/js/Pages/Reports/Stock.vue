<script setup>
import { computed, inject, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VBadge from '../../Components/ui/VBadge.vue';
import VButton from '../../Components/ui/VButton.vue';
import VPagination from '../../Components/ui/VPagination.vue';
import VTable from '../../Components/ui/VTable.vue';
import { formatCurrency, formatNumber } from '../../utils/format';

const props = defineProps({
    products: { type: Object, required: true },
    summary: { type: Object, default: () => ({ total_units: 0, total_value: 0, active_count: 0, low_stock_count: 0 }) },
    filters: { type: Object, default: () => ({}) },
});

const route = inject('route');

const filters = ref({
    keyword: props.filters.keyword ?? '',
    low_stock: props.filters.low_stock !== undefined && props.filters.low_stock !== null
        ? String(props.filters.low_stock)
        : '',
});

const lowStockOptions = computed(() => [
    { value: '', label: 'Semua' },
    { value: '1', label: 'Stok menipis' },
]);

const columns = [
    { key: 'sku', label: 'SKU' },
    { key: 'name', label: 'Nama' },
    { key: 'category', label: 'Kategori' },
    { key: 'stock', label: 'Stok' },
    { key: 'minimum_stock', label: 'Stok Min.' },
    { key: 'status', label: 'Status' },
];

function isLowStock(product) {
    return Number(product.stock) <= Number(product.minimum_stock);
}

function applyFilters() {
    const params = {};

    if (filters.value.keyword) {
        params.keyword = filters.value.keyword;
    }
    if (filters.value.low_stock !== '') {
        params.low_stock = filters.value.low_stock;
    }

    router.get(route('reports.stock', params), {}, { preserveState: true, preserveScroll: true, replace: true });
}
</script>

<template>
    <Head title="Laporan Stok" />

    <AppLayout>
        <div class="page-header">
            <h1 class="page-title">Laporan Stok</h1>
            <VButton variant="ghost" :href="route('reports.index')">← Ringkasan Laporan</VButton>
        </div>

        <div class="summary-grid" aria-label="Ringkasan stok">
            <div class="summary-card">
                <span class="summary-card__label">Total Unit</span>
                <span class="summary-card__value">{{ formatNumber(summary.total_units ?? 0) }}</span>
            </div>
            <div class="summary-card">
                <span class="summary-card__label">Nilai Stok</span>
                <span class="summary-card__value">{{ formatCurrency(summary.total_value ?? 0) }}</span>
            </div>
            <div class="summary-card">
                <span class="summary-card__label">Produk Aktif</span>
                <span class="summary-card__value">{{ formatNumber(summary.active_count ?? 0) }}</span>
            </div>
            <div class="summary-card">
                <span class="summary-card__label">Stok Menipis</span>
                <span class="summary-card__value">{{ formatNumber(summary.low_stock_count ?? 0) }}</span>
            </div>
        </div>

        <form class="filters" @submit.prevent="applyFilters">
            <div class="form__group">
                <label for="keyword">Cari</label>
                <input id="keyword" v-model.trim="filters.keyword" type="text" placeholder="SKU atau nama" />
            </div>

            <div class="form__group">
                <label for="low_stock">Status Stok</label>
                <select id="low_stock" v-model="filters.low_stock">
                    <option v-for="option in lowStockOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <div class="form__actions">
                <VButton type="submit" variant="ghost">Terapkan Filter</VButton>
                <VButton variant="ghost" :href="route('reports.stock')">Reset</VButton>
            </div>
        </form>

        <VTable :columns="columns" :rows="products.data ?? []">
            <template #category="{ row }">{{ row.category?.name ?? '—' }}</template>

            <template #status="{ row }">
                <VBadge :variant="isLowStock(row) ? 'danger' : 'success'">
                    {{ isLowStock(row) ? 'Stok menipis' : 'Aman' }}
                </VBadge>
            </template>

            <template #empty>
                <p>Belum ada produk.</p>
            </template>
        </VTable>

        <VPagination :links="products.links ?? []" />
    </AppLayout>
</template>