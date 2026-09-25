<script setup>
import { computed, inject } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VButton from '../../Components/ui/VButton.vue';
import VFormField from '../../Components/ui/VFormField.vue';
import VSelect from '../../Components/ui/VSelect.vue';
import { formatCurrency, todayISO } from '../../utils/format';

const props = defineProps({
    suppliers: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
});

const route = inject('route');

function blankItem() {
    return { product_id: '', quantity: '', unit_price: '' };
}

const form = useForm({
    supplier_id: '',
    purchase_date: todayISO(),
    notes: '',
    items: [blankItem()],
});

const supplierOptions = computed(() => [
    { value: '', label: 'Pilih supplier' },
    ...props.suppliers.map((supplier) => ({ value: String(supplier.id), label: supplier.name })),
]);

const productOptions = computed(() => [
    { value: '', label: 'Pilih produk' },
    ...props.products.map((product) => ({
        value: String(product.id),
        label: `${product.name} (${product.sku})`,
    })),
]);

const productMap = computed(() => {
    const map = {};

    for (const product of props.products) {
        map[String(product.id)] = product;
    }

    return map;
});

const itemErrors = computed(() => {
    const items = [];

    for (const index of Object.keys(form.items)) {
        items[index] = {
            product_id: form.errors[`items.${index}.product_id`],
            quantity: form.errors[`items.${index}.quantity`],
            unit_price: form.errors[`items.${index}.unit_price`],
        };
    }

    return items;
});

const grandTotal = computed(() => form.items.reduce((total, item) => {
    const subtotal = Math.round((Number(item.quantity) || 0) * (Number(item.unit_price) || 0) * 100) / 100;

    return total + subtotal;
}, 0));

function subtotal(item) {
    return Math.round((Number(item.quantity) || 0) * (Number(item.unit_price) || 0) * 100) / 100;
}

function onProductChange(item) {
    const product = productMap.value[item.product_id];

    if (product && item.unit_price === '') {
        item.unit_price = String(product.purchase_price);
    }
}

function addRow() {
    form.items.push(blankItem());
}

function removeRow(index) {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
}

function submit() {
    form.post(route('purchases.store'));
}
</script>

<template>
    <Head title="Tambah Penerimaan" />

    <AppLayout>
        <h1 class="page-title">Tambah Penerimaan</h1>
        <p class="muted">Penerimaan disimpan sebagai draft. Finalisasi setelah form disimpan untuk menambah stok.</p>

        <form class="form" @submit.prevent="submit">
            <div class="form__grid">
                <VSelect
                    id="supplier_id"
                    label="Supplier"
                    v-model="form.supplier_id"
                    :options="supplierOptions"
                    :error="form.errors.supplier_id"
                    required
                />

                <VFormField
                    id="purchase_date"
                    label="Tanggal"
                    type="date"
                    v-model="form.purchase_date"
                    :error="form.errors.purchase_date"
                    required
                />

                <VFormField
                    id="notes"
                    label="Catatan (opsional)"
                    v-model="form.notes"
                    :error="form.errors.notes"
                />
            </div>

            <h2 class="section-title">Detail Produk</h2>
            <div v-if="form.errors.items" class="flash flash--error" role="alert">
                {{ form.errors.items }}
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Harga Beli</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in form.items" :key="index" class="item-row">
                            <td>
                                <select
                                    v-model="item.product_id"
                                    :aria-label="`Produk baris ${index + 1}`"
                                    @change="onProductChange(item)"
                                >
                                    <option v-for="option in productOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <span v-if="itemErrors[index].product_id" class="form__error">
                                    {{ itemErrors[index].product_id }}
                                </span>
                            </td>
                            <td>
                                <input
                                    v-model="item.quantity"
                                    type="number"
                                    step="0.001"
                                    min="0.001"
                                    :aria-label="`Jumlah baris ${index + 1}`"
                                />
                                <span v-if="itemErrors[index].quantity" class="form__error">
                                    {{ itemErrors[index].quantity }}
                                </span>
                            </td>
                            <td>
                                <input
                                    v-model="item.unit_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :aria-label="`Harga beli baris ${index + 1}`"
                                />
                                <span v-if="itemErrors[index].unit_price" class="form__error">
                                    {{ itemErrors[index].unit_price }}
                                </span>
                            </td>
                            <td class="item-subtotal">{{ formatCurrency(subtotal(item)) }}</td>
                            <td>
                                <VButton variant="ghost" size="sm" @click="removeRow(index)">Hapus</VButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="form__actions">
                <VButton type="button" variant="ghost" @click="addRow">Tambah Baris</VButton>
                <strong class="grand-total">Total: {{ formatCurrency(grandTotal) }}</strong>
            </div>

            <div class="form__actions">
                <VButton type="submit" :disabled="form.processing">Simpan Penerimaan</VButton>
                <VButton variant="ghost" :href="route('purchases.index')">Batal</VButton>
            </div>
        </form>
    </AppLayout>
</template>