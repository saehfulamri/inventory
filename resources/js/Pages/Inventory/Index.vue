<script setup>
import { computed, inject, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VBadge from '../../Components/ui/VBadge.vue';
import VButton from '../../Components/ui/VButton.vue';
import VPagination from '../../Components/ui/VPagination.vue';
import VSelect from '../../Components/ui/VSelect.vue';
import VTable from '../../Components/ui/VTable.vue';

const props = defineProps({
    products: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    categories: { type: Array, default: () => [] },
});

const route = inject('route');
const page = usePage();
const can = computed(() => page.props.can ?? {});

const filters = ref({
    keyword: props.filters.keyword ?? '',
    category_id: props.filters.category_id !== undefined && props.filters.category_id !== null
        ? String(props.filters.category_id)
        : '',
    low_stock: Boolean(props.filters.low_stock),
});

const categoryOptions = computed(() => [
    { value: '', label: 'Semua' },
    ...props.categories.map((category) => ({ value: String(category.id), label: category.name })),
]);

const columns = [
    { key: 'sku', label: 'SKU' },
    { key: 'name', label: 'Nama' },
    { key: 'category', label: 'Kategori' },
    { key: 'unit', label: 'Satuan' },
    { key: 'stock', label: 'Stok' },
    { key: 'minimum_stock', label: 'Stok Min.' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Aksi' },
];

function isLowStock(product) {
    return Number(product.stock) <= Number(product.minimum_stock);
}

function applyFilters() {
    const params = {};

    if (filters.value.keyword) {
        params.keyword = filters.value.keyword;
    }
    if (filters.value.category_id !== '') {
        params.category_id = filters.value.category_id;
    }
    if (filters.value.low_stock) {
        params.low_stock = '1';
    }

    router.get(route('inventory.index', params), {}, { preserveState: true, preserveScroll: true, replace: true });
}
</script>

<template>
    <Head title="Stok" />

    <AppLayout>
        <div class="page-header">
            <h1 class="page-title">Stok</h1>
            <div class="row-actions">
                <VButton variant="ghost" :href="route('inventory.movements')">Riwayat Pergerakan</VButton>
                <VButton v-if="can.viewAnyProducts" :href="route('inventory.adjustments.create')">Penyesuaian Stok</VButton>
            </div>
        </div>

        <form class="filters" @submit.prevent="applyFilters">
            <div class="form__group">
                <label for="keyword">Cari</label>
                <input
                    id="keyword"
                    v-model.trim="filters.keyword"
                    type="text"
                    placeholder="SKU, barcode, atau nama"
                />
            </div>

            <VSelect
                id="category_id"
                label="Kategori"
                v-model="filters.category_id"
                :options="categoryOptions"
            />

            <label class="checkbox form__group">
                <input v-model="filters.low_stock" type="checkbox" value="1" />
                Hanya stok menipis
            </label>

            <div class="form__actions">
                <VButton type="submit" variant="ghost">Terapkan Filter</VButton>
            </div>
        </form>

        <VTable :columns="columns" :rows="products.data ?? []">
            <template #category="{ row }">{{ row.category?.name ?? '—' }}</template>

            <template #unit="{ row }">{{ row.unit?.name ?? '—' }}</template>

            <template #status="{ row }">
                <VBadge :variant="isLowStock(row) ? 'danger' : 'success'">
                    {{ isLowStock(row) ? 'Stok menipis' : 'Aman' }}
                </VBadge>
            </template>

            <template #actions="{ row }">
                <div class="row-actions">
                    <VButton
                        variant="ghost"
                        size="sm"
                        :href="route('inventory.movements', { product_id: row.id })"
                    >Riwayat</VButton>
                    <VButton
                        v-if="can.viewAnyProducts"
                        variant="ghost"
                        size="sm"
                        :href="route('inventory.adjustments.create', { product_id: row.id })"
                    >Sesuaikan</VButton>
                </div>
            </template>

            <template #empty>
                <p>Belum ada produk.</p>
                <p class="muted">Tambahkan produk terlebih dahulu untuk melihat stok.</p>
            </template>
        </VTable>

        <VPagination :links="products.links ?? []" />
    </AppLayout>
</template>