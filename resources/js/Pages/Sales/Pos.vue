<script setup>
import { computed, inject, onMounted, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Components/Layouts/AppLayout.vue';
import VButton from '../../Components/ui/VButton.vue';
import VSelect from '../../Components/ui/VSelect.vue';
import { formatCurrency, todayISO } from '../../utils/format';

const props = defineProps({
    paymentMethods: { type: Array, default: () => [] }, // [{ value, label }]
});

const route = inject('route');

const form = useForm({
    sale_date: todayISO(),
    payment_method: 'cash',
    paid_amount: '',
});

const query = ref('');
const products = ref([]);
const loading = ref(false);
const error = ref(false);
const cart = ref([]); // { id, name, price, stock, image, qty }

const paymentMethodOptions = computed(() => props.paymentMethods);

const isCash = computed(() => form.payment_method === 'cash');

const subtotal = computed(() => cart.value.reduce((sum, item) => sum + (item.price * item.qty), 0));

const grandTotal = computed(() => subtotal.value);

const change = computed(() => {
    if (form.payment_method !== 'cash') {
        return null;
    }

    const paid = Number(form.paid_amount);

    if (!Number.isFinite(paid)) {
        return null;
    }

    return paid - grandTotal.value;
});

function inCartQty(product) {
    const item = cart.value.find((entry) => entry.id === product.id);

    return item ? item.qty : 0;
}

function soldOut(product) {
    return inCartQty(product) >= product.stock;
}

async function searchProducts() {
    loading.value = true;
    error.value = false;

    try {
        const response = await fetch(route('sales.products', { q: query.value.trim() }), {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Gagal memuat produk.');
        }

        products.value = await response.json();
    } catch {
        error.value = true;
        products.value = [];
    } finally {
        loading.value = false;
    }
}

function addToCart(product) {
    const existing = cart.value.find((item) => item.id === product.id);

    if (existing) {
        if (existing.qty < product.stock) {
            existing.qty += 1;
        }

        return;
    }

    cart.value.push({
        id: product.id,
        name: product.name,
        price: product.price,
        stock: product.stock,
        image: product.image_url,
        qty: 1,
    });
}

function clampQty(item) {
    let qty = Number(item.qty);

    if (!Number.isFinite(qty) || qty < 0.001) {
        qty = 1;
    }

    if (qty > item.stock) {
        qty = item.stock;
    }

    item.qty = qty;
}

function removeFromCart(index) {
    cart.value.splice(index, 1);
}

function pay() {
    form.transform((data) => ({
        ...data,
        items: cart.value.map((item) => ({ product_id: item.id, quantity: item.qty })),
    }));

    form.post(route('sales.store'));
}

onMounted(searchProducts);
</script>

<template>
    <Head title="Penjualan — Transaksi Baru" />

    <AppLayout>
        <h1 class="page-title">Penjualan</h1>
        <p class="muted">Cari produk, tambahkan ke keranjang, lalu selesaikan pembayaran.</p>

        <form class="form" @submit.prevent="pay">
            <div class="pos-layout">
                <section class="pos-pane" aria-label="Pencarian produk">
                    <h2 class="section-title">Produk</h2>

                    <div class="form__group pos-search">
                        <label for="product-search">Cari produk</label>
                        <div class="pos-search__row">
                            <input
                                id="product-search"
                                v-model="query"
                                type="search"
                                placeholder="Nama, SKU, atau barcode"
                                autocomplete="off"
                                @keydown.enter.prevent="searchProducts"
                            />
                            <VButton variant="ghost" @click="searchProducts">Cari</VButton>
                        </div>
                    </div>

                    <ul class="product-list" aria-live="polite">
                        <li v-if="loading" class="empty-state">
                            <p>Loading produk…</p>
                        </li>
                        <li v-else-if="error" class="empty-state">
                            <p>Gagal memuat produk.</p>
                        </li>
                        <li v-else-if="products.length === 0" class="empty-state">
                            <p>Tidak ada produk ditemukan.</p>
                        </li>

                        <li v-for="product in products" :key="product.id" class="product-item">
                            <img
                                v-if="product.image_url"
                                :src="product.image_url"
                                alt=""
                                class="product-item__thumb"
                                loading="lazy"
                            />
                            <span
                                v-else
                                class="product-item__thumb product-item__thumb--empty"
                                aria-hidden="true"
                            ></span>

                            <div class="product-item__info">
                                <span class="product-item__name">{{ product.name }}</span>
                                <span class="product-item__meta">
                                    {{ product.sku }} · {{ formatCurrency(product.price) }} · Stok {{ product.stock }}
                                </span>
                            </div>

                            <button
                                type="button"
                                class="btn btn--sm product-item__add"
                                :disabled="soldOut(product)"
                                @click="addToCart(product)"
                            >
                                {{ soldOut(product) ? 'Habis' : 'Tambah' }}
                            </button>
                        </li>
                    </ul>
                </section>

                <section class="pos-pane" aria-label="Keranjang dan pembayaran">
                    <h2 class="section-title">Keranjang</h2>

                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="cart.length === 0">
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <p>Keranjang masih kosong.</p>
                                            <p class="muted">Tambahkan produk dari daftar di sisi kiri.</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="(item, index) in cart" :key="item.id">
                                    <td>
                                        <img v-if="item.image" :src="item.image" alt="" class="cart-thumb" />
                                        {{ item.name }}
                                    </td>
                                    <td class="cart-price">{{ formatCurrency(item.price) }}</td>
                                    <td>
                                        <input
                                            v-model.number="item.qty"
                                            class="cart-qty"
                                            type="number"
                                            step="0.001"
                                            min="1"
                                            :max="item.stock"
                                            :aria-label="`Jumlah ${item.name}`"
                                            @input="clampQty(item)"
                                        />
                                    </td>
                                    <td class="cart-line-total">{{ formatCurrency(item.price * item.qty) }}</td>
                                    <td>
                                        <VButton
                                            variant="danger"
                                            size="sm"
                                            :aria-label="`Hapus ${item.name}`"
                                            @click="removeFromCart(index)"
                                        >Hapus</VButton>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="cart-summary">
                        <div class="cart-summary__row">
                            <span>Subtotal</span>
                            <output id="cart-subtotal">{{ formatCurrency(subtotal) }}</output>
                        </div>
                        <div class="cart-summary__row">
                            <span>Total</span>
                            <strong id="cart-grand-total">{{ formatCurrency(grandTotal) }}</strong>
                        </div>
                    </div>

                    <div class="form__grid">
                        <VSelect
                            id="payment_method"
                            label="Metode Pembayaran"
                            v-model="form.payment_method"
                            :options="paymentMethodOptions"
                            :error="form.errors.payment_method"
                            required
                        />

                        <div v-if="isCash" class="form__group">
                            <label for="paid_amount">Jumlah Bayar</label>
                            <input
                                id="paid_amount"
                                v-model="form.paid_amount"
                                type="number"
                                step="0.01"
                                min="0"
                                :required="isCash"
                            />
                            <span v-if="form.errors.paid_amount" class="form__error">
                                {{ form.errors.paid_amount }}
                            </span>
                        </div>

                        <div class="form__group">
                            <label for="change">Kembalian</label>
                            <output id="change" class="stock-figure">
                                {{ change === null ? '—' : formatCurrency(change) }}
                            </output>
                        </div>
                    </div>

                    <div class="form__actions">
                        <VButton type="submit" :disabled="cart.length === 0 || form.processing">Bayar</VButton>
                        <VButton variant="ghost" :href="route('sales.index')">Batal</VButton>
                    </div>
                </section>
            </div>
        </form>
    </AppLayout>
</template>