<script setup>
import { computed, inject, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VBadge from '../../Components/ui/VBadge.vue';
import VButton from '../../Components/ui/VButton.vue';
import VConfirmDialog from '../../Components/ui/VConfirmDialog.vue';
import { formatCurrency, formatDate } from '../../utils/format';

const props = defineProps({
    purchase: { type: Object, required: true },
});

const route = inject('route');
const page = usePage();
const can = computed(() => page.props.can ?? {});

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

const confirmFinalize = ref(false);

function finalize() {
    router.post(route('purchases.finalize', props.purchase.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Detail Penerimaan" />

    <AppLayout>
        <div class="page-header">
            <h1 class="page-title">Penerimaan {{ purchase.purchase_number }}</h1>
            <VButton variant="ghost" :href="route('purchases.index')">Kembali</VButton>
        </div>

        <div class="detail-card">
            <dl class="detail-list">
                <dt>Status</dt>
                <dd>
                    <VBadge :variant="statusVariants[purchase.status] ?? 'muted'">
                        {{ statusLabels[purchase.status] ?? purchase.status }}
                    </VBadge>
                </dd>
                <dt>Supplier</dt>
                <dd>{{ purchase.supplier?.name ?? '—' }}</dd>
                <dt>Tanggal</dt>
                <dd>{{ formatDate(purchase.purchase_date) }}</dd>
                <dt>Dibuat oleh</dt>
                <dd>{{ purchase.user?.name ?? '—' }}</dd>
                <dt>Catatan</dt>
                <dd>{{ purchase.notes || '-' }}</dd>
            </dl>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Beli</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in purchase.items ?? []" :key="item.id">
                        <td>{{ item.product?.name ?? '—' }} ({{ item.product?.sku ?? '—' }})</td>
                        <td>{{ item.quantity }}</td>
                        <td>{{ formatCurrency(item.unit_price) }}</td>
                        <td>{{ formatCurrency(item.subtotal) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <th>{{ formatCurrency(purchase.total_amount) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div
            v-if="can.viewAnyPurchases && purchase.status === 'draft'"
            class="form__actions"
        >
            <VButton @click="confirmFinalize = true">Finalisasi Penerimaan</VButton>
        </div>

        <VConfirmDialog
            :open="confirmFinalize"
            title="Finalisasi penerimaan"
            message="Finalisasi penerimaan ini? Stok akan ditambahkan dan data tidak dapat diubah."
            confirm-label="Finalisasi"
            @confirm="finalize"
            @cancel="confirmFinalize = false"
        />
    </AppLayout>
</template>