<script setup>
import { computed, inject, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VBadge from '../../Components/ui/VBadge.vue';
import VButton from '../../Components/ui/VButton.vue';
import VConfirmDialog from '../../Components/ui/VConfirmDialog.vue';
import VPagination from '../../Components/ui/VPagination.vue';
import VSelect from '../../Components/ui/VSelect.vue';
import VTable from '../../Components/ui/VTable.vue';
import { formatCurrency } from '../../utils/format.js';

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
    is_active: props.filters.is_active !== undefined && props.filters.is_active !== null
        ? String(props.filters.is_active)
        : '',
    low_stock: Boolean(props.filters.low_stock),
});

const categoryOptions = computed(() => [
    { value: '', label: 'Semua' },
    ...props.categories.map((category) => ({ value: String(category.id), label: category.name })),
]);

const statusOptions = [
    { value: '', label: 'Semua' },
    { value: '1', label: 'Aktif' },
    { value: '0', label: 'Nonaktif' },
];

const columns = [
    { key: 'photo', label: 'Foto' },
    { key: 'sku', label: 'SKU' },
    { key: 'barcode', label: 'Barcode' },
    { key: 'name', label: 'Nama' },
    { key: 'category', label: 'Kategori' },
    { key: 'purchase_price', label: 'Harga Beli' },
    { key: 'selling_price', label: 'Harga Jual' },
    { key: 'stock', label: 'Stok' },
    { key: 'minimum_stock', label: 'Stok Min.' },
    { key: 'is_active', label: 'Status' },
    { key: 'actions', label: 'Aksi' },
];

const rows = computed(() => props.products.data ?? []);

const deactivating = ref(null);

const deactivateMessage = computed(() => deactivating.value
    ? `Nonaktifkan produk "${deactivating.value.name}"?`
    : '');

function applyFilters() {
    const params = {};

    if (filters.value.keyword) {
        params.keyword = filters.value.keyword;
    }
    if (filters.value.category_id) {
        params.category_id = filters.value.category_id;
    }
    if (filters.value.is_active !== '') {
        params.is_active = filters.value.is_active;
    }
    if (filters.value.low_stock) {
        params.low_stock = 1;
    }

    router.get(route('products.index', params), {}, { preserveState: true, preserveScroll: true, replace: true });
}

function confirmDeactivate(product) {
    deactivating.value = product;
}

function deactivate() {
    router.post(route('products.deactivate', deactivating.value.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Produk" />

    <AppLayout>
        <div class="page-header">
            <h1 class="page-title">Produk</h1>
            <VButton v-if="can.viewAnyProducts" :href="route('products.create')">Tambah Produk</VButton>
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

            <VSelect
                id="is_active"
                label="Status"
                v-model="filters.is_active"
                :options="statusOptions"
            />

            <div class="form__group">
                <label class="checkbox">
                    <input v-model="filters.low_stock" type="checkbox" />
                    Stok minimum
                </label>
            </div>

            <div class="form__actions">
                <VButton type="submit" variant="ghost">Terapkan Filter</VButton>
            </div>
        </form>

        <VTable :columns="columns" :rows="rows">
            <template #photo="{ row }">
                <img
                    v-if="row.image_url"
                    :src="row.image_url"
                    :alt="`Foto ${row.name}`"
                    class="thumb"
                    loading="lazy"
                />
                <span v-else class="thumb thumb--empty" aria-hidden="true">—</span>
            </template>

            <template #category="{ row }">{{ row.category?.name }}</template>

            <template #purchase_price="{ row }">{{ formatCurrency(row.purchase_price) }}</template>
            <template #selling_price="{ row }">{{ formatCurrency(row.selling_price) }}</template>

            <template #is_active="{ row }">
                <VBadge :variant="row.is_active ? 'success' : 'danger'">
                    {{ row.is_active ? 'Aktif' : 'Nonaktif' }}
                </VBadge>
            </template>

            <template #actions="{ row }">
                <div class="row-actions">
                    <VButton
                        v-if="can.viewAnyProducts"
                        variant="ghost"
                        size="sm"
                        :href="route('products.edit', row.id)"
                    >Edit</VButton>
                    <VButton
                        v-if="can.viewAnyProducts && row.is_active"
                        variant="danger"
                        size="sm"
                        @click="confirmDeactivate(row)"
                    >Nonaktifkan</VButton>
                </div>
            </template>

            <template #empty>
                <p>Belum ada produk.</p>
                <p class="muted">Tambahkan produk pertama untuk mulai mengelola inventori.</p>
            </template>
        </VTable>

        <VPagination :links="products.links ?? []" />

        <VConfirmDialog
            :open="Boolean(deactivating)"
            title="Nonaktifkan produk"
            :message="deactivateMessage"
            confirm-label="Nonaktifkan"
            @confirm="deactivate"
            @cancel="deactivating = null"
        />
    </AppLayout>
</template>