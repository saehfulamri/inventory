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
    categories: { type: Object, required: true },
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
    { key: 'name', label: 'Nama' },
    { key: 'description', label: 'Deskripsi' },
    { key: 'is_active', label: 'Status' },
    { key: 'actions', label: 'Aksi' },
];

const rows = computed(() => props.categories.data ?? []);

const deactivating = ref(null);

const deactivateMessage = computed(() => deactivating.value
    ? `Nonaktifkan kategori "${deactivating.value.name}"?`
    : '');

function applyFilters() {
    const params = {};

    if (filters.value.keyword) {
        params.keyword = filters.value.keyword;
    }
    if (filters.value.is_active !== '') {
        params.is_active = filters.value.is_active;
    }

    router.get(route('categories.index', params), {}, { preserveState: true, preserveScroll: true, replace: true });
}

function confirmDeactivate(category) {
    deactivating.value = category;
}

function deactivate() {
    router.post(route('categories.deactivate', deactivating.value.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Kategori" />

    <AppLayout>
        <div class="page-header">
            <h1 class="page-title">Kategori</h1>
            <VButton v-if="can.viewAnyCategories" :href="route('categories.create')">Tambah Kategori</VButton>
        </div>

        <form class="filters" @submit.prevent="applyFilters">
            <div class="form__group">
                <label for="keyword">Cari</label>
                <input
                    id="keyword"
                    v-model.trim="filters.keyword"
                    type="text"
                    placeholder="Nama kategori"
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
            <template #description="{ row }">{{ row.description || '—' }}</template>

            <template #is_active="{ row }">
                <VBadge :variant="row.is_active ? 'success' : 'danger'">
                    {{ row.is_active ? 'Aktif' : 'Nonaktif' }}
                </VBadge>
            </template>

            <template #actions="{ row }">
                <div class="row-actions">
                    <VButton
                        v-if="can.viewAnyCategories"
                        variant="ghost"
                        size="sm"
                        :href="route('categories.edit', row.id)"
                    >Edit</VButton>
                    <VButton
                        v-if="can.viewAnyCategories && row.is_active"
                        variant="danger"
                        size="sm"
                        @click="confirmDeactivate(row)"
                    >Nonaktifkan</VButton>
                </div>
            </template>

            <template #empty>
                <p>Belum ada kategori.</p>
                <p class="muted">Tambahkan kategori pertama untuk mengelompokkan produk.</p>
            </template>
        </VTable>

        <VPagination :links="categories.links ?? []" />

        <VConfirmDialog
            :open="Boolean(deactivating)"
            title="Nonaktifkan kategori"
            :message="deactivateMessage"
            confirm-label="Nonaktifkan"
            @confirm="deactivate"
            @cancel="deactivating = null"
        />
    </AppLayout>
</template>
