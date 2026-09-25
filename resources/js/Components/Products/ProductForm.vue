<script setup>
import { computed, inject, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import VButton from '../ui/VButton.vue';
import VFormField from '../ui/VFormField.vue';
import VSelect from '../ui/VSelect.vue';

const props = defineProps({
    product: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
    units: { type: Array, default: () => [] },
});

const route = inject('route');

const form = useForm({
    name: props.product?.name ?? '',
    sku: props.product?.sku ?? '',
    barcode: props.product?.barcode ?? '',
    category_id: props.product ? String(props.product.category_id) : '',
    unit_id: props.product ? String(props.product.unit_id) : '',
    purchase_price: props.product ? String(props.product.purchase_price) : '',
    selling_price: props.product ? String(props.product.selling_price) : '',
    minimum_stock: props.product ? String(props.product.minimum_stock) : '',
    image_path: null,
    is_active: props.product ? Boolean(props.product.is_active) : true,
});

const categoryOptions = computed(() => [
    { value: '', label: 'Pilih kategori' },
    ...props.categories.map((category) => ({ value: String(category.id), label: category.name })),
]);

const unitOptions = computed(() => [
    { value: '', label: 'Pilih satuan' },
    ...props.units.map((unit) => ({ value: String(unit.id), label: `${unit.name} (${unit.symbol})` })),
]);

const submitLabel = computed(() => (props.product ? 'Simpan Perubahan' : 'Simpan Produk'));

const imagePreviewUrl = ref(props.product?.image_url ?? null);
let objectUrl = null;

function onImageChange(event) {
    const file = event.target.files?.[0] ?? null;

    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
        objectUrl = null;
    }

    if (file) {
        objectUrl = URL.createObjectURL(file);
        imagePreviewUrl.value = objectUrl;
    } else {
        imagePreviewUrl.value = props.product?.image_url ?? null;
    }

    form.image_path = file;
}

function submit() {
    if (props.product) {
        form.put(route('products.update', props.product.id));

        return;
    }

    form.post(route('products.store'));
}
</script>

<template>
    <form class="form" @submit.prevent="submit">
        <div class="form__grid">
            <VFormField
                id="name"
                label="Nama Produk"
                v-model="form.name"
                :error="form.errors.name"
                required
            />

            <VFormField
                id="sku"
                label="SKU / Kode"
                v-model="form.sku"
                :error="form.errors.sku"
                required
            />

            <VFormField
                id="barcode"
                label="Barcode (opsional)"
                v-model="form.barcode"
                :error="form.errors.barcode"
            />

            <VSelect
                id="category_id"
                label="Kategori"
                v-model="form.category_id"
                :options="categoryOptions"
                :error="form.errors.category_id"
                required
            />

            <VSelect
                id="unit_id"
                label="Satuan"
                v-model="form.unit_id"
                :options="unitOptions"
                :error="form.errors.unit_id"
                required
            />

            <VFormField
                id="purchase_price"
                label="Harga Beli"
                type="number"
                min="0"
                step="0.01"
                v-model="form.purchase_price"
                :error="form.errors.purchase_price"
                required
            />

            <VFormField
                id="selling_price"
                label="Harga Jual"
                type="number"
                min="0"
                step="0.01"
                v-model="form.selling_price"
                :error="form.errors.selling_price"
                required
            />

            <VFormField
                id="minimum_stock"
                label="Stok Minimum"
                type="number"
                min="0"
                step="0.001"
                v-model="form.minimum_stock"
                :error="form.errors.minimum_stock"
                required
            />

            <div class="form__group">
                <label for="image_path">Foto Produk (opsional)</label>
                <img
                    v-if="imagePreviewUrl"
                    :src="imagePreviewUrl"
                    :alt="`Foto ${form.name || 'Produk'}`"
                    class="product-form__preview"
                />
                <input
                    id="image_path"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    :aria-invalid="form.errors.image_path ? 'true' : 'false'"
                    :aria-describedby="form.errors.image_path ? 'image_path-error' : undefined"
                    @change="onImageChange"
                />
                <p class="muted">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                <span v-if="form.errors.image_path" id="image_path-error" class="form__error">
                    {{ form.errors.image_path }}
                </span>
            </div>

            <div class="form__group">
                <label class="checkbox">
                    <input v-model="form.is_active" type="checkbox" />
                    Produk aktif
                </label>
            </div>
        </div>

        <div class="form__actions">
            <VButton type="submit" :disabled="form.processing">{{ submitLabel }}</VButton>
            <VButton variant="ghost" :href="route('products.index')">Batal</VButton>
        </div>
    </form>
</template>