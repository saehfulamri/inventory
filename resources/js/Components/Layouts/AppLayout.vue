<script setup>
import { ref, computed, inject, nextTick } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import VFlash from '../ui/VFlash.vue';
import VNavIcon from '../ui/VNavIcon.vue';

// injected Ziggy route helper
const route = inject('route');
const page = usePage();

const brandName = 'PRESMA MART';
const user = computed(() => page.props.auth?.user ?? null);
const can = computed(() => page.props.can ?? {});
const flash = computed(() => page.props.flash ?? {});

// Single source of truth for sidebar and bottom navigation.
// `primary` items are pinned to the bottom bar; the rest go under "Lainnya".
const navItems = computed(() => [
    { label: 'Dashboard', icon: 'dashboard', route: 'dashboard', match: 'dashboard', primary: true, show: true },
    { label: 'Penjualan', icon: 'sales', route: 'sales.index', match: 'sales.*', primary: true, show: can.value.viewAnySales },
    { label: 'Produk', icon: 'products', route: 'products.index', match: 'products.*', primary: true, show: can.value.viewAnyProducts },
    { label: 'Stok', icon: 'inventory', route: 'inventory.index', match: 'inventory.*', primary: true, show: can.value.viewAnyProducts },
    { label: 'Penerimaan', icon: 'purchases', route: 'purchases.index', match: 'purchases.*', show: can.value.viewAnyPurchases },
    { label: 'Supplier', icon: 'suppliers', route: 'suppliers.index', match: 'suppliers.*', show: can.value.viewAnySuppliers },
    { label: 'Kategori', icon: 'categories', route: 'categories.index', match: 'categories.*', show: can.value.viewAnyCategories },
    { label: 'Satuan', icon: 'units', route: 'units.index', match: 'units.*', show: can.value.viewAnyUnits },
    { label: 'Laporan', icon: 'reports', route: 'reports.index', match: 'reports.*', show: can.value.viewReports },
    { label: 'Pengguna', icon: 'users', route: 'users.index', match: 'users.*', show: can.value.manageUsers },
].filter((item) => item.show));

const MAX_BOTTOM_ITEMS = 5;
const hasMoreMenu = computed(() => navItems.value.length > MAX_BOTTOM_ITEMS);
const bottomItems = computed(() => (hasMoreMenu.value
    ? navItems.value.filter((item) => item.primary).slice(0, MAX_BOTTOM_ITEMS - 1)
    : navItems.value));
const moreItems = computed(() => navItems.value.filter((item) => !bottomItems.value.includes(item)));
const isMoreActive = computed(() => moreItems.value.some((item) => isActive(item)));

function isActive(item) {
    return route().current(item.match);
}

// "Lainnya" sheet (bottom navigation, below lg)
const isMoreOpen = ref(false);
const moreButton = ref(null);
const moreSheet = ref(null);

async function openMore() {
    isMoreOpen.value = true;
    await nextTick();
    moreSheet.value?.focus();
}

function closeMore() {
    if (!isMoreOpen.value) {
        return;
    }
    isMoreOpen.value = false;
    moreButton.value?.focus();
}

function trapFocus(event) {
    const focusable = moreSheet.value?.querySelectorAll('a[href], button:not([disabled])');
    if (!focusable?.length) {
        return;
    }
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && (document.activeElement === first || document.activeElement === moreSheet.value)) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
}

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <a href="#main" class="skip-link">Lewati ke konten utama</a>

    <aside v-if="user" class="sidebar hidden lg:flex">
        <Link :href="route('dashboard')" class="brand">{{ brandName }}</Link>

        <nav class="sidebar__nav" aria-label="Navigasi utama">
            <Link
                v-for="item in navItems"
                :key="item.route"
                :href="route(item.route)"
                class="nav-link"
                :class="{ 'nav-link--active': isActive(item) }"
                :aria-current="isActive(item) ? 'page' : undefined"
            ><VNavIcon :name="item.icon" />{{ item.label }}</Link>
        </nav>

        <div class="sidebar__user">
            <div class="sidebar__user-info">
                <p class="sidebar__user-name">{{ user.name }}</p>
                <p class="sidebar__user-role">{{ user.role?.label }}</p>
            </div>
            <button type="button" class="btn--nav" @click="logout">Keluar</button>
        </div>
    </aside>

    <div class="app-content lg:pl-60">
        <div v-if="flash.success" class="container" role="status" aria-live="polite">
            <VFlash type="success">{{ flash.success }}</VFlash>
        </div>

        <div v-if="flash.error" class="container" role="alert" aria-live="assertive">
            <VFlash type="error">{{ flash.error }}</VFlash>
        </div>

        <main id="main" class="container" tabindex="-1">
            <slot />
        </main>
    </div>

    <nav v-if="user" class="bottom-nav lg:hidden" aria-label="Navigasi bawah">
        <Link
            v-for="item in bottomItems"
            :key="item.route"
            :href="route(item.route)"
            class="bottom-nav__item"
            :class="{ 'bottom-nav__item--active': isActive(item) }"
            :aria-current="isActive(item) ? 'page' : undefined"
            :aria-label="item.label"
            :data-tooltip="item.label"
        ><VNavIcon :name="item.icon" /></Link>

        <button
            v-if="hasMoreMenu"
            ref="moreButton"
            type="button"
            class="bottom-nav__item"
            :class="{ 'bottom-nav__item--active': isMoreActive }"
            aria-haspopup="dialog"
            aria-controls="more-nav"
            :aria-expanded="isMoreOpen"
            aria-label="Lainnya"
            data-tooltip="Lainnya"
            @click="openMore"
        ><VNavIcon name="more" /></button>
    </nav>

    <div
        v-if="isMoreOpen"
        id="more-nav"
        class="more-sheet lg:hidden"
        role="dialog"
        aria-modal="true"
        aria-label="Menu lainnya"
        @keydown.escape="closeMore"
        @keydown.tab="trapFocus"
    >
        <div class="more-sheet__overlay" @click="closeMore"></div>
        <div ref="moreSheet" class="more-sheet__panel" tabindex="-1">
            <nav aria-label="Menu lainnya">
                <Link
                    v-for="item in moreItems"
                    :key="item.route"
                    :href="route(item.route)"
                    class="nav-link"
                    :class="{ 'nav-link--active': isActive(item) }"
                    :aria-current="isActive(item) ? 'page' : undefined"
                    @click="closeMore"
                ><VNavIcon :name="item.icon" />{{ item.label }}</Link>
            </nav>
            <div class="more-sheet__user">
                <p class="sidebar__user-name">{{ user?.name }} <span class="sidebar__user-role">· {{ user?.role?.label }}</span></p>
                <button type="button" class="btn--nav" @click="logout">Keluar</button>
            </div>
            <button type="button" class="btn--nav" @click="closeMore">Tutup</button>
        </div>
    </div>
</template>
