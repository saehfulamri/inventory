<script setup>
import { computed, inject } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import VFlash from '../ui/VFlash.vue';

const route = inject('route');
const page = usePage();

const appName = computed(() => page.props.app?.name ?? 'Sistem Inventori & Penjualan');
const user = computed(() => page.props.auth?.user ?? null);
const can = computed(() => page.props.can ?? {});
const flash = computed(() => page.props.flash ?? {});

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <a href="#main" class="skip-link">Lewati ke konten utama</a>

    <header class="topbar">
        <div class="container topbar__inner">
            <Link :href="route('dashboard')" class="brand">
                {{ appName }}
            </Link>

            <nav v-if="user" class="topnav" aria-label="Navigasi utama">
                <div class="topnav__links">
                    <Link :href="route('dashboard')">Dashboard</Link>
                    <Link v-if="can.viewAnyProducts" :href="route('products.index')">Produk</Link>
                    <Link v-if="can.viewAnyPurchases" :href="route('purchases.index')">Penerimaan</Link>
                    <Link v-if="can.viewAnySales" :href="route('sales.index')">Penjualan</Link>
                    <Link v-if="can.viewAnyProducts" :href="route('inventory.index')">Stok</Link>
                    <Link v-if="can.viewReports" :href="route('reports.index')">Laporan</Link>
                </div>

                <span class="topnav__user">{{ user.name }}</span>
                <button type="button" class="btn--nav" @click="logout">Keluar</button>
            </nav>
        </div>
    </header>

    <div v-if="flash.success" class="container">
        <VFlash type="success">{{ flash.success }}</VFlash>
    </div>

    <div v-if="flash.error" class="container">
        <VFlash type="error">{{ flash.error }}</VFlash>
    </div>

    <main id="main" class="container" tabindex="-1">
        <slot />
    </main>
</template>