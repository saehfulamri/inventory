<script setup>
import { defineProps } from 'vue';
import VBadge from './VBadge.vue';

const props = defineProps({
    lowStock: { type: Array, required: true },
    canView: { type: Boolean, default: false },
    route: { type: Function, required: true },
});
</script>

<template>
    <section class="dashboard-panel p-4 bg-white rounded shadow" aria-labelledby="low-stock-title">
        <div class="dashboard-panel__header">
            <h2 class="section-title" id="low-stock-title">Produk Stok Menipis</h2>
            <Link v-if="canView" :href="route('inventory.index', { low_stock: 1 })" class="text-link">Lihat Semua</Link>
        </div>

        <ul v-if="lowStock.length" class="low-stock-list">
            <li v-for="product in lowStock" :key="product.id" class="low-stock-item">
                <span class="low-stock-item__info">
                    <span class="low-stock-item__name">{{ product.name }}</span>
                    <span class="low-stock-item__meta">{{ product.sku }}</span>
                </span>
                <VBadge variant="danger">Stok {{ product.stock }} / min {{ product.minimum_stock }}</VBadge>
            </li>
        </ul>
        <p v-else class="muted">Semua produk berada di atas stok minimum.</p>
    </section>
</template>
