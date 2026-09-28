<script setup>
import { computed, inject } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VBadge from '../../Components/ui/VBadge.vue';
import { formatCurrency } from '../../composables/useFormat.js';

const props = defineProps({
    summary: { type: Object, required: true },
});

const route = inject('route');
const page = usePage();

const user = computed(() => page.props.auth?.user ?? null);
const can = computed(() => page.props.can ?? {});

const todayTotal = computed(() => formatCurrency(props.summary.today_total));
const chartTotal = computed(() => formatCurrency(
    props.summary.chart.reduce((sum, point) => sum + (point.total ?? 0), 0),
));
const chartMax = computed(() => Math.max(1, ...props.summary.chart.map((point) => point.total ?? 0)));

/**
 * Pre-compute bar heights and formatted currency values to avoid repeated
 * inline function calls during re-render.
 *
 * @type {import('vue').ComputedRef<Array<{date: string, label: string, day: string, total: number, formattedTotal: string, barHeight: string}>>}
 */
const chartPoints = computed(() =>
    props.summary.chart.map((point) => {
        const height = (point.total > 0)
            ? `${Math.max(6, Math.round((point.total / chartMax.value) * 100))}%`
            : '2px';

        return {
            ...point,
            formattedTotal: formatCurrency(point.total),
            barHeight: height,
        };
    }),
);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="page-header">
            <h1 class="page-title">Dashboard</h1>
            <p class="muted">Selamat datang, {{ user?.name }}.</p>
        </div>

        <!-- ======================================================
             Summary cards (#6 semantic wrappers, #7 responsive grid)
             ====================================================== -->
        <section
            class="summary-grid grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mt-6"
            aria-label="Ringkasan Kinerja"
        >
            <article class="summary-card p-4 bg-white rounded shadow" aria-labelledby="card-sales-today">
                <h3 id="card-sales-today" class="summary-card__label text-sm font-medium text-gray-500">Penjualan Hari Ini</h3>
                <span class="summary-card__value block text-2xl font-semibold text-gray-900">{{ todayTotal }}</span>
                <span class="summary-card__meta block text-xs text-gray-400">{{ summary.today_count }} transaksi</span>
            </article>

            <article class="summary-card p-4 bg-white rounded shadow" aria-labelledby="card-transactions-today">
                <h3 id="card-transactions-today" class="summary-card__label text-sm font-medium text-gray-500">Transaksi Hari Ini</h3>
                <span class="summary-card__value block text-2xl font-semibold text-gray-900">{{ summary.today_count }}</span>
                <span class="summary-card__meta block text-xs text-gray-400">Selesai</span>
            </article>

            <article class="summary-card p-4 bg-white rounded shadow" aria-labelledby="card-active-products">
                <h3 id="card-active-products" class="summary-card__label text-sm font-medium text-gray-500">Produk Aktif</h3>
                <span class="summary-card__value block text-2xl font-semibold text-gray-900">{{ summary.active_products }}</span>
                <span class="summary-card__meta block text-xs text-gray-400">Nama produk terdaftar</span>
            </article>

            <article class="summary-card p-4 bg-white rounded shadow" aria-labelledby="card-low-stock">
                <h3 id="card-low-stock" class="summary-card__label text-sm font-medium text-gray-500">Stok Menipis</h3>
                <span class="summary-card__value block text-2xl font-semibold text-gray-900">{{ summary.low_stock_count }}</span>
                <span class="summary-card__meta block text-xs text-gray-400">Stok ≤ minimum</span>
            </article>
        </section>

        <!-- ======================================================
             Dashboard panels (#6 semantic, #7 responsive grid)
             ====================================================== -->
        <div class="dashboard-grid grid gap-6 mt-6 md:grid-cols-2 lg:grid-cols-3">
            <section class="dashboard-panel p-4 bg-white rounded shadow" aria-labelledby="chart-title">
                <div class="dashboard-panel__header">
                    <h2 class="section-title" id="chart-title">Penjualan 7 Hari Terakhir</h2>
                </div>

                <p class="muted chart-summary">Total: {{ chartTotal }}</p>

                <!-- Chart — #13 (Sprint 3): keyboard nav & aria-label per bar -->
                <ul class="chart" aria-label="Grafik batang penjualan 7 hari terakhir">
                    <li
                        v-for="point in chartPoints"
                        :key="point.date"
                        class="chart__col"
                        tabindex="0"
                        :aria-label="`${point.label}: ${point.formattedTotal}`"
                    >
                        <span class="chart__value" aria-hidden="true">{{ point.formattedTotal }}</span>
                        <span class="chart__track" aria-hidden="true">
                            <span class="chart__bar" :style="{ height: point.barHeight }"></span>
                        </span>
                        <span class="chart__label" aria-hidden="true">{{ point.label }}<br>{{ point.day }}</span>
                    </li>
                </ul>
            </section>

            <section class="dashboard-panel p-4 bg-white rounded shadow" aria-labelledby="low-stock-title">
                <div class="dashboard-panel__header">
                    <h2 class="section-title" id="low-stock-title">Produk Stok Menipis</h2>
                    <Link
                        v-if="can.viewAnyProducts"
                        :href="route('inventory.index', { low_stock: 1 })"
                        class="text-link"
                    >Lihat Semua</Link>
                </div>

                <ul v-if="summary.low_stock.length" class="low-stock-list">
                    <li v-for="product in summary.low_stock" :key="product.id" class="low-stock-item">
                        <span class="low-stock-item__info">
                            <span class="low-stock-item__name">{{ product.name }}</span>
                            <span class="low-stock-item__meta">{{ product.sku }}</span>
                        </span>
                        <VBadge variant="danger" :aria-label="`Stok rendah: ${product.stock} dari minimum ${product.minimum_stock}`">
                            Stok {{ product.stock }} / min {{ product.minimum_stock }}
                        </VBadge>
                    </li>
                </ul>
                <p v-else class="muted">Semua produk berada di atas stok minimum.</p>
            </section>
        </div>
    </AppLayout>
</template>