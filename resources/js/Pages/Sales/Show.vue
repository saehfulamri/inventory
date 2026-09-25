<script setup>
import { computed, inject } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VBadge from '../../Components/ui/VBadge.vue';
import VButton from '../../Components/ui/VButton.vue';
import { formatCurrency, formatDate } from '../../utils/format';

const props = defineProps({
    sale: { type: Object, required: true },
});

const route = inject('route');
const page = usePage();
const appName = computed(() => page.props.app?.name ?? 'Sistem Inventori & Penjualan');

const statusLabels = {
    draft: 'Draft',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
};

const statusVariants = {
    draft: 'warning',
    completed: 'success',
    cancelled: 'danger',
};

const paymentMethodLabels = {
    cash: 'Tunai',
    transfer: 'Transfer',
    qris: 'QRIS',
    card: 'Kartu',
};
</script>

<template>
    <Head :title="`Struk ${sale.sale_number}`" />

    <AppLayout>
        <div class="page-header print-hidden">
            <h1 class="page-title">Struk Penjualan</h1>
            <div class="row-actions">
                <VButton @click="window.print()">Cetak Struk</VButton>
                <VButton variant="ghost" :href="route('sales.index')">Kembali</VButton>
            </div>
        </div>

        <div class="detail-card receipt">
            <header class="receipt__header">
                <strong class="receipt__brand">{{ appName }}</strong>
                <span class="receipt__meta">Nomor: {{ sale.sale_number }}</span>
                <span class="receipt__meta">Tanggal: {{ formatDate(sale.sale_date) }}</span>
                <span class="receipt__meta">Kasir: {{ sale.user?.name ?? '—' }}</span>
                <span class="receipt__meta">
                    <VBadge :variant="statusVariants[sale.status] ?? 'muted'">
                        {{ statusLabels[sale.status] ?? sale.status }}
                    </VBadge>
                </span>
            </header>

            <div class="table-wrap">
                <table class="table receipt__table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in sale.items ?? []" :key="item.id">
                            <td>{{ item.product?.name ?? '—' }}</td>
                            <td>{{ item.quantity }}</td>
                            <td>{{ formatCurrency(item.unit_price) }}</td>
                            <td>{{ formatCurrency(item.subtotal) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <dl class="receipt__totals">
                <div class="receipt__row">
                    <dt>Subtotal</dt>
                    <dd>{{ formatCurrency(sale.subtotal) }}</dd>
                </div>
                <div class="receipt__row">
                    <dt>Total</dt>
                    <dd>{{ formatCurrency(sale.grand_total) }}</dd>
                </div>
                <div class="receipt__row">
                    <dt>Metode</dt>
                    <dd>{{ paymentMethodLabels[sale.payment_method] ?? sale.payment_method }}</dd>
                </div>
                <div class="receipt__row">
                    <dt>Dibayar</dt>
                    <dd>{{ formatCurrency(sale.paid_amount) }}</dd>
                </div>
                <div class="receipt__row">
                    <dt>Kembalian</dt>
                    <dd>{{ formatCurrency(sale.change_amount) }}</dd>
                </div>
            </dl>

            <p class="receipt__footer muted">Terima kasih atas kunjungan Anda.</p>
        </div>
    </AppLayout>
</template>