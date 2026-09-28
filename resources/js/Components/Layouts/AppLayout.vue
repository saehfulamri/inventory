<script setup>
import { ref, computed, inject } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import VFlash from '../ui/VFlash.vue';

// injected Ziggy route helper
const route = inject('route');
const page = usePage();

// Mobile navigation drawer state
const isMobileNavOpen = ref(false);
function toggleMobileNav() { isMobileNavOpen.value = !isMobileNavOpen.value; }
function closeMobileNav() { isMobileNavOpen.value = false; }

// Helper to determine active route (Ziggy integration)
function isActive(name) {
    // `page.component` contains the Vue component name like 'Dashboard/Index'
    // We consider a route active if the component path starts with the given name (case‑insensitive).
    return typeof page.component === 'string' && page.component.toLowerCase().startsWith(name.toLowerCase());
}

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

<button type="button" class="btn--nav md:hidden" @click="toggleMobileNav" aria-label="Toggle navigation" :aria-expanded="isMobileNavOpen" aria-controls="mobile-nav">☰</button>
<nav v-if="user" class="topnav" aria-label="Navigasi utama">
                <div class="topnav__links hidden md:flex">
                    <Link :href="route('dashboard')" :class="{ 'text-primary font-medium': isActive('dashboard') }">Dashboard</Link>
                    <Link v-if="can.viewAnyProducts" :href="route('products.index')" :class="{ 'text-primary font-medium': isActive('products.index') }">Produk</Link>
                    <Link v-if="can.viewAnySuppliers" :href="route('suppliers.index')" :class="{ 'text-primary font-medium': isActive('suppliers.index') }">Supplier</Link>
                    <Link v-if="can.viewAnyPurchases" :href="route('purchases.index')" :class="{ 'text-primary font-medium': isActive('purchases.index') }">Penerimaan</Link>
                    <Link v-if="can.viewAnySales" :href="route('sales.index')" :class="{ 'text-primary font-medium': isActive('sales.index') }">Penjualan</Link>
                    <Link v-if="can.viewAnyProducts" :href="route('inventory.index')" :class="{ 'text-primary font-medium': isActive('inventory.index') }">Stok</Link>
                    <Link v-if="can.viewReports" :href="route('reports.index')" :class="{ 'text-primary font-medium': isActive('reports.index') }">Laporan</Link>
                </div>

                <span class="topnav__user">{{ user.name }}</span>
                <button type="button" class="btn--nav" @click="logout">Keluar</button>
            </nav>
        </div>
    </header>

    <!-- Mobile navigation drawer -->
<div v-if="isMobileNavOpen" class="fixed inset-0 z-40 flex" id="mobile-nav">
  <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeMobileNav"></div>
  <nav class="bg-white w-64 p-4 overflow-y-auto">
    <div class="flex flex-col space-y-2">
      <Link :href="route('dashboard')" @click="closeMobileNav" :class="{ 'text-primary font-medium': isActive('dashboard') }">Dashboard</Link>
      <Link v-if="can.viewAnyProducts" :href="route('products.index')" @click="closeMobileNav" :class="{ 'text-primary font-medium': isActive('products.index') }">Produk</Link>
      <Link v-if="can.viewAnySuppliers" :href="route('suppliers.index')" @click="closeMobileNav" :class="{ 'text-primary font-medium': isActive('suppliers.index') }">Supplier</Link>
      <Link v-if="can.viewAnyPurchases" :href="route('purchases.index')" @click="closeMobileNav" :class="{ 'text-primary font-medium': isActive('purchases.index') }">Penerimaan</Link>
      <Link v-if="can.viewAnySales" :href="route('sales.index')" @click="closeMobileNav" :class="{ 'text-primary font-medium': isActive('sales.index') }">Penjualan</Link>
      <Link v-if="can.viewAnyProducts" :href="route('inventory.index')" @click="closeMobileNav" :class="{ 'text-primary font-medium': isActive('inventory.index') }">Stok</Link>
      <Link v-if="can.viewReports" :href="route('reports.index')" @click="closeMobileNav" :class="{ 'text-primary font-medium': isActive('reports.index') }">Laporan</Link>
    </div>
  </nav>
</div>
<div v-if="flash.success" class="container" role="status" aria-live="polite">
        <VFlash type="success">{{ flash.success }}</VFlash>
    </div>

    <div v-if="flash.error" class="container" role="alert" aria-live="assertive">
        <VFlash type="error">{{ flash.error }}</VFlash>
    </div>

    <main id="main" class="container" tabindex="-1">
        <slot />
    </main>
</template>