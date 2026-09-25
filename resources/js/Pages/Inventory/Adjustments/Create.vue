<script setup>
import { computed, inject } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Components/Layouts/AppLayout.vue';
import VButton from '../../../Components/ui/VButton.vue';
import VFormField from '../../../Components/ui/VFormField.vue';
import VSelect from '../../../Components/ui/VSelect.vue';
import { formatQuantity } from '../../../utils/format';

const props = defineProps({
    products: { type: Array, default: () => [] },
    selectedProductId: { type: Number, default: null },
});

const route = inject('route');

const form = useForm({
    product_id: props.selectedProductId ? String(props.selectedProductId) : '',
    new_stock: '',
    reason: '',
});

const productOptions = computed(() => [
    { value: '', label: 'Pilih produk' },
    ...props.products.map((product) => ({
        value: String(product.id),
        label: `${product.name} (${product.sku})`,
    })),
]);

const selectedProduct = computed(() => props.products.find((product) => String(product.id) === form.product_id));

const currentStock = computed(() => {
    if (!selectedProduct.value) {
        return null;
    }

    return Number(selectedProduct.value.stock);
});

const difference = computed(() => {
    if (currentStock.value === null || form.new_stock === '') {
        return null;
    }

    const value = Math.round((Number(form.new_stock) - currentStock.value) * 1000) / 1000;

    return value;
});

function submit() {
    form.post(route('inventory.adjustments.store'));
}
</script>

<template>
    <Head title="Penyesuaian Stok" />

    <AppLayout>
        <h1 class="page-title">Penyesuaian Stok</h1>
        <p class="muted">
            Gunakan penyesuaian untuk mencocokkan stok sistem dengan hasil perhitungan fisik (stok opname).
        </p>

        <form class="form" @submit.prevent="submit">
            <VSelect
                id="product_id"
                label="Produk"
                v-model="form.product_id"
                :options="productOptions"
                :error="form.errors.product_id"
                required
            />

            <div class="form__grid">
                <div class="form__group">
                    <label for="current_stock">Stok Saat Ini</label>
                    <output id="current_stock" class="stock-figure" aria-live="polite">
                        {{ currentStock === null ? '—' : formatQuantity(currentStock) }}
                    </output>
                </div>

                <VFormField
                    id="new_stock"
                    label="Stok Baru"
                    type="number"
                    step="0.001"
                    min="0"
                    v-model="form.new_stock"
                    :error="form.errors.new_stock"
                    required
                />

                <div class="form__group">
                    <label for="difference">Selisih</label>
                    <output id="difference" class="stock-figure" aria-live="polite">
                        {{ difference === null ? '—' : (difference > 0 ? '+' : '') + formatQuantity(difference) }}
                    </output>
                </div>
            </div>

            <VFormField
                id="reason"
                label="Alasan Penyesuaian"
                type="textarea"
                v-model="form.reason"
                :error="form.errors.reason"
                required
            />

            <div class="form__actions">
                <VButton type="submit" :disabled="form.processing">Simpan Penyesuaian</VButton>
                <VButton variant="ghost" :href="route('inventory.index')">Batal</VButton>
            </div>
        </form>
    </AppLayout>
</template>