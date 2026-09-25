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
    sales: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statuses: { type: Array, default: () => [] }, // [{ value, label }]
    paymentMethods: { type: Array, default: () => [] }, // [{ value, label }]
});

const route = inject('route');
const page = usePage();
const can = computed(() => page.props.can ?? {});

const filters = ref({
    keyword: props.filters.keyword ?? '',
    status: props.filters.status ?? '',
    payment_method: props.filters.payment_method ?? '',
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
});

const statusOptions = computed(() => [
    { value: '', label: 'Semua' },
    ...props.statuses,
]);

const paymentMethodOptions = computed(() => [
    { value: '', label: 'Semua' },
    ...props.paymentMethods,
]);

const statusLabels = computed(() => {
    const map = {};

    for (const status of props.statuses) {
        map[status.value] = status.label;
    }

    return map;
});

const paymentMethodLabels = computed(() => {
    const map = {};

    for (const method of props.paymentMethods) {
        map[method.value] = method.label;
    }

    return map;
});

const statusVariants = {
    draft: 'warning',
    completed: 'success',
    cancelled: 'danger',
};

const columns = [
    { key: 'sale_number', label: 'No. Penjualan' },
    { key: 'sale_date', label: 'Tanggal' },
    { key: 'user', label: 'Kasir' },
    { key: 'grand_total', label: 'Total' },
    { key: 'payment_method', label: 'Metode' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Aksi' },
];

function applyFilters() {
    const params = {};

    if (filters.value.keyword) {
        params.keyword = filters.value.keyword;
    }
    if (filters.value.status) {
        params.status = filters.value.status;
    }
    if (filters.value.payment_method) {
        params.payment_method = filters.value.payment_method;
    }
    if (filters.value.date_from) {
        params.date_from = filters.value.date_from;
    }
    if (filters.value.date_to) {
        params.date_to = filters.value.date_to;
    }

    router.get(route('sales.index', params), {}, { preserveState: true, preserveScroll: true, replace: true });
}
</script>

<template>
    <Head title="Penjualan" />

    <AppLayout>
        <div class="page-header">
            <h1 class="page-title">Penjualan</h1>
            <VButton v-if="can.viewAnySales" :href="route('sales.create')">Transaksi Baru</VButton>
        </div>

        <form class="filters" @submit.prevent="applyFilters">
            <div class="form__group">
                <label for="keyword">Cari</label>
                <input
                    id="keyword"
                    v-model.trim="filters.keyword"
                    type="text"
                    placeholder="Nomor penjualan"
                />
            </div>

            <VSelect
                id="status"
                label="Status"
                v-model="filters.status"
                :options="statusOptions"
            />

            <VSelect
                id="payment_method"
                label="Metode"
                v-model="filters.payment_method"
                :options="paymentMethodOptions"
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
            </div>
        </form>

        <VTable :columns="columns" :rows="sales.data ?? []">
            <template #sale_date="{ row }">{{ formatDate(row.sale_date) }}</template>

            <template #user="{ row }">{{ row.user?.name ?? '—' }}</template>

            <template #grand_total="{ row }">{{ formatCurrency(row.grand_total) }}</template>

            <template #payment_method="{ row }">
                {{ paymentMethodLabels[row.payment_method] ?? row.payment_method }}
            </template>

            <template #status="{ row }">
                <VBadge :variant="statusVariants[row.status] ?? 'muted'">
                    {{ statusLabels[row.status] ?? row.status }}
                </VBadge>
            </template>

            <template #actions="{ row }">
                <VButton
                    variant="ghost"
                    size="sm"
                    :href="route('sales.show', row.id)"
                >Struk</VButton>
            </template>

            <template #empty>
                <p>Belum ada penjualan.</p>
                <p class="muted">Mulai transaksi pertama dari halaman penjualan.</p>
            </template>
        </VTable>

        <VPagination :links="sales.links ?? []" />
    </AppLayout>
</template>