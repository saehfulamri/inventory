<script setup>
import { computed, inject } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VBadge from '../../Components/ui/VBadge.vue';

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

function barHeight(point) {
    if (!(point.total > 0)) {
        return '2px';
    }

    return `${Math.max(6, Math.round((point.total / chartMax.value) * 100))}%`;
}

function formatCurrency(value) {
    return `Rp ${new Intl.NumberFormat('id-ID').format(Math.trunc(value ?? 0))}`;
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="page-header">
            <h1 class="page-title">Dashboard</h1>
            <p class="muted">Selamat datang, {{ user?.name }}.</p>
        </div>

        <section class="summary-grid" aria-label="Ringkasan">
            <div class="summary-card">
                <span class="summary-card__label">Penjualan Hari Ini</span>
                <span class="summary-card__value">{{ todayTotal }}</span>
                <span class="summary-card__meta">{{ summary.today_count }} transaksi</span>
            </div>

            <div class="summary-card">
                <span class="summary-card__label">Transaksi Hari Ini</span>
                <span class="summary-card__value">{{ summary.today_count }}</span>
                <span class="summary-card__meta">Selesai</span>
            </div>

            <div class="summary-card">
                <span class="summary-card__label">Produk Aktif</span>
                <span class="summary-card__value">{{ summary.active_products }}</span>
                <span class="summary-card__meta">Nama produk terdaftar</span>
            </div>

            <div class="summary-card">
                <span class="summary-card__label">Stok Menipis</span>
                <span class="summary-card__value">{{ summary.low_stock_count }}</span>
                <span class="summary-card__meta">Stok ≤ minimum</span>
            </div>
        </section>

        <div class="dashboard-grid">
            <section class="dashboard-panel" aria-labelledby="chart-title">
                <div class="dashboard-panel__header">
                    <h2 class="section-title" id="chart-title">Penjualan 7 Hari Terakhir</h2>
                </div>

                <p class="muted chart-summary">Total: {{ chartTotal }}</p>

                <ul class="chart" aria-label="Grafik penjualan 7 hari terakhir">
                    <li v-for="point in summary.chart" :key="point.date" class="chart__col">
                        <span class="chart__value">{{ formatCurrency(point.total) }}</span>
                        <span class="chart__track">
                            <span class="chart__bar" :style="{ height: barHeight(point) }"></span>
                        </span>
                        <span class="chart__label">{{ point.label }}<br>{{ point.day }}</span>
                    </li>
                </ul>
            </section>

            <section class="dashboard-panel" aria-labelledby="low-stock-title">
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
                        <VBadge variant="danger">Stok {{ product.stock }} / min {{ product.minimum_stock }}</VBadge>
                    </li>
                </ul>
                <p v-else class="muted">Semua produk berada di atas stok minimum.</p>
            </section>
        </div>
    </AppLayout>
</template>