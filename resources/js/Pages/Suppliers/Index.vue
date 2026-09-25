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

const props = defineProps({
    suppliers: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const route = inject('route');
const page = usePage();
const can = computed(() => page.props.can ?? {});

const filters = ref({
    keyword: props.filters.keyword ?? '',
    is_active: props.filters.is_active !== undefined && props.filters.is_active !== null
        ? String(props.filters.is_active)
        : '',
});

const statusOptions = [
    { value: '', label: 'Semua' },
    { value: '1', label: 'Aktif' },
    { value: '0', label: 'Nonaktif' },
];

const columns = [
    { key: 'code', label: 'Kode' },
    { key: 'name', label: 'Nama' },
    { key: 'contact', label: 'Kontak' },
    { key: 'address', label: 'Alamat' },
    { key: 'is_active', label: 'Status' },
    { key: 'actions', label: 'Aksi' },
];

const rows = computed(() => props.suppliers.data ?? []);

const deactivating = ref(null);

const deactivateMessage = computed(() => deactivating.value
    ? `Nonaktifkan supplier "${deactivating.value.name}"?`
    : '');

function applyFilters() {
    const params = {};

    if (filters.value.keyword) {
        params.keyword = filters.value.keyword;
    }
    if (filters.value.is_active !== '') {
        params.is_active = filters.value.is_active;
    }

    router.get(route('suppliers.index', params), {}, { preserveState: true, preserveScroll: true, replace: true });
}

function confirmDeactivate(supplier) {
    deactivating.value = supplier;
}

function deactivate() {
    router.post(route('suppliers.deactivate', deactivating.value.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Supplier" />

    <AppLayout>
        <div class="page-header">
            <h1 class="page-title">Supplier</h1>
            <VButton v-if="can.viewAnySuppliers" :href="route('suppliers.create')">Tambah Supplier</VButton>
        </div>

        <form class="filters" @submit.prevent="applyFilters">
            <div class="form__group">
                <label for="keyword">Cari</label>
                <input
                    id="keyword"
                    v-model.trim="filters.keyword"
                    type="text"
                    placeholder="Nama atau kode"
                />
            </div>

            <VSelect
                id="is_active"
                label="Status"
                v-model="filters.is_active"
                :options="statusOptions"
            />

            <div class="form__actions">
                <VButton type="submit" variant="ghost">Terapkan Filter</VButton>
            </div>
        </form>

        <VTable :columns="columns" :rows="rows">
            <template #code="{ row }">{{ row.code || '—' }}</template>

            <template #contact="{ row }">
                <span v-if="row.phone">{{ row.phone }}<br /></span>
                <span v-if="row.email">{{ row.email }}</span>
            </template>

            <template #is_active="{ row }">
                <VBadge :variant="row.is_active ? 'success' : 'danger'">
                    {{ row.is_active ? 'Aktif' : 'Nonaktif' }}
                </VBadge>
            </template>

            <template #actions="{ row }">
                <div class="row-actions">
                    <VButton
                        v-if="can.viewAnySuppliers"
                        variant="ghost"
                        size="sm"
                        :href="route('suppliers.edit', row.id)"
                    >Edit</VButton>
                    <VButton
                        v-if="can.viewAnySuppliers && row.is_active"
                        variant="danger"
                        size="sm"
                        @click="confirmDeactivate(row)"
                    >Nonaktifkan</VButton>
                </div>
            </template>

            <template #empty>
                <p>Belum ada supplier.</p>
                <p class="muted">Tambahkan supplier pertama untuk mulai mengelola penerimaan barang.</p>
            </template>
        </VTable>

        <VPagination :links="suppliers.links ?? []" />

        <VConfirmDialog
            :open="Boolean(deactivating)"
            title="Nonaktifkan supplier"
            :message="deactivateMessage"
            confirm-label="Nonaktifkan"
            @confirm="deactivate"
            @cancel="deactivating = null"
        />
    </AppLayout>
</template>