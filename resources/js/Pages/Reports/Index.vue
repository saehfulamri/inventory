<script setup>
import { inject } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import { formatCurrency, formatNumber } from '../../utils/format';

defineProps({
    sales: { type: Object, default: () => ({ total: 0, count: 0 }) },
    purchases: { type: Object, default: () => ({ total: 0, count: 0 }) },
    stock: { type: Object, default: () => ({ total_units: 0, active_count: 0 }) },
    movements: { type: Object, default: () => ({ units: 0 }) },
});

const route = inject('route');
</script>

<template>
    <Head title="Laporan" />

    <AppLayout>
        <div class="page-header">
            <h1 class="page-title">Laporan</h1>
            <p class="muted">Ringkasan penjualan, pembelian, stok, dan pergerakan stok dalam satu tempat.</p>
        </div>

        <div class="card-grid">
            <Link :href="route('reports.sales')" class="report-card">
                <span class="report-card__title">Penjualan</span>
                <span class="report-card__value">{{ formatCurrency(sales.total ?? 0) }}</span>
                <span class="report-card__meta">{{ formatNumber(sales.count ?? 0) }} transaksi</span>
            </Link>

            <Link :href="route('reports.purchases')" class="report-card">
                <span class="report-card__title">Pembelian</span>
                <span class="report-card__value">{{ formatCurrency(purchases.total ?? 0) }}</span>
                <span class="report-card__meta">{{ formatNumber(purchases.count ?? 0) }} transaksi</span>
            </Link>

            <Link :href="route('reports.stock')" class="report-card">
                <span class="report-card__title">Stok</span>
                <span class="report-card__value">{{ formatNumber(stock.total_units ?? 0) }}</span>
                <span class="report-card__meta">unit tercatat</span>
            </Link>

            <Link :href="route('reports.movements')" class="report-card">
                <span class="report-card__title">Pergerakan Stok</span>
                <span class="report-card__value">{{ formatNumber(movements.units ?? 0) }}</span>
                <span class="report-card__meta">unit bergerak</span>
            </Link>
        </div>
    </AppLayout>
</template>