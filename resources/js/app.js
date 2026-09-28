import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { Ziggy as ziggyConfig } from './ziggy';

const appName = 'Sistem Inventori & Penjualan';

createInertiaApp({
    title: (title) => (title ? `${title} — ${appName}` : appName),
    resolve: (name) => {
        // Critical pages (eager-load): Dashboard, Products, Sales, Inventory
        const criticalPages = import.meta.glob('./Pages/{Dashboard,Products,Sales,Inventory}/**/*.vue');
        // Rarely-visited pages (lazy/dynamic chunk): Reports, Suppliers, Purchases, Auth
        const lazyPages = import.meta.glob('./Pages/{Reports,Suppliers,Purchases,Auth}/**/*.vue');

        const key = `./Pages/${name}.vue`;
        if (criticalPages[key]) {
            return resolvePageComponent(key, criticalPages);
        }
        return resolvePageComponent(key, lazyPages);
    },

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, ziggyConfig)
            .mount(el);
    },
});