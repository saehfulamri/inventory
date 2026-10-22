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
import { activeStatusOptions, buildParams, filterString } from '../../utils/filters.js';

const props = defineProps({
    units: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const route = inject('route');
const page = usePage();
const can = computed(() => page.props.can ?? {});

const filters = ref({
    keyword: props.filters.keyword ?? '',
    is_active: filterString(props.filters.is_active),
});

const statusOptions = activeStatusOptions();

const columns = [
    { key: 'name', label: 'Nama' },
    { key: 'symbol', label: 'Simbol' },
    { key: 'is_active', label: 'Status' },
    { key: 'actions', label: 'Aksi' },
];

const rows = computed(() => props.units.data ?? []);

const deactivating = ref(null);

const deactivateMessage = computed(() => deactivating.value
    ? `Nonaktifkan satuan "${deactivating.value.name}"?`
    : '');

function applyFilters() {
    const params = buildParams(filters.value);

    router.get(route('units.index', params), {}, { preserveState: true, preserveScroll: true, replace: true });
}

function confirmDeactivate(unit) {
    deactivating.value = unit;
}

function deactivate() {
    router.post(route('units.deactivate', deactivating.value.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Satuan" />

    <AppLayout>
        <div class="page-header">
            <h1 class="page-title">Satuan</h1>
            <VButton v-if="can.viewAnyUnits" :href="route('units.create')">Tambah Satuan</VButton>
        </div>

        <form class="filters" @submit.prevent="applyFilters">
            <div class="form__group">
                <label for="keyword">Cari</label>
                <input
                    id="keyword"
                    v-model.trim="filters.keyword"
                    type="text"
                    placeholder="Nama atau simbol"
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
            <template #is_active="{ row }">
                <VBadge :variant="row.is_active ? 'success' : 'danger'">
                    {{ row.is_active ? 'Aktif' : 'Nonaktif' }}
                </VBadge>
            </template>

            <template #actions="{ row }">
                <div class="row-actions">
                    <VButton
                        v-if="can.viewAnyUnits"
                        variant="ghost"
                        size="sm"
                        :href="route('units.edit', row.id)"
                    >Edit</VButton>
                    <VButton
                        v-if="can.viewAnyUnits && row.is_active"
                        variant="danger"
                        size="sm"
                        @click="confirmDeactivate(row)"
                    >Nonaktifkan</VButton>
                </div>
            </template>

            <template #empty>
                <p>Belum ada satuan.</p>
                <p class="muted">Tambahkan satuan pertama (mis. pcs, box, kg) untuk produk.</p>
            </template>
        </VTable>

        <VPagination :links="units.links ?? []" />

        <VConfirmDialog
            :open="Boolean(deactivating)"
            title="Nonaktifkan satuan"
            :message="deactivateMessage"
            confirm-label="Nonaktifkan"
            @confirm="deactivate"
            @cancel="deactivating = null"
        />
    </AppLayout>
</template>
