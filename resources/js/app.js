import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { Ziggy as ziggyConfig } from './ziggy';

const appName = 'Sistem Inventori & Penjualan';

createInertiaApp({
    title: (title) => (title ? `${title} — ${appName}` : appName),
    // Vite automatically code-splits each page into its own chunk.
    // A single glob is the correct approach — do NOT split into multiple globs
    // as that prevents Tailwind v4 from scanning all Vue files via the module graph.
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue'),
    ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, ziggyConfig)
            .mount(el);
    },
});