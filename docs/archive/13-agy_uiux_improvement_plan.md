# UI/UX Improvement Plan for **inventory** (Laravel + Inertia + Vue + Tailwind)

## Overview
The codebase already follows many good practices (Inertia layout, skip‑link, component‑based UI).  The main gaps are:
- Responsive navigation & layout
- Full semantic markup & accessibility (ARIA roles, headings, contrast)
- Consistent design tokens & theming
- Light‑weight performance tweaks (lazy loading, live regions)
- Mobile‑first navigation drawer

The plan is organised by **priority** and grouped into logical work‑streams.

---

## 📌 High‑Priority (must ship before next release)
| # | Task | Files / Components | Description | Effort (dev‑days) | Test/Verification |
|---|------|-------------------|-------------|-------------------|-------------------|
| 1 | **Add ARIA roles & live regions to flash messages** | `resources/js/Components/Layouts/AppLayout.vue` (lines 45‑51) | Add `role="status"` and `aria-live="polite"` to success/error containers; ensure they are announced by screen readers. | 0.5 | Manual aXe/Lighthouse audit; visual regression test for markup change. |
| 2 | **Responsive navigation bar** | `resources/js/Components/Layouts/AppLayout.vue` | Implement a mobile hamburger button that toggles a drawer (`MobileNav.vue`). Use Tailwind utilities (`hidden md:flex`, `fixed inset-0`, `bg-opacity-75`). | 1.5 | Cypress/E2E test on viewport breakpoints; aXe check for focus trap. |
| 3 | **Active navigation state** | `AppLayout.vue` navigation `<Link>` elements | Compute `isActive` via `usePage().url` or Inertia `route().current()` and apply `active` class (`border-b-2 border-primary`). | 0.5 | Visual test; snapshot diff. |
| 4 | **Contrast audit for badge variants** | `resources/js/Components/ui/VBadge.vue` & Tailwind config | Verify that `badge--muted`, `badge--success`, `badge--warning`, `badge--danger` meet WCAG 2.1 AA (≥ 4.5:1). Adjust Tailwind colors if needed. | 0.5 | aXe/Lighthouse contrast report. |
| 5 | **Add `role="alert"` to flash containers** (combined with #1) | Same as #1 | Ensure error flash uses `role="alert"` for immediate announcement. | — | — |

---

## 📌 Medium‑Priority (enhance usability & maintainability)
| # | Task | Files / Components | Description | Effort (dev‑days) | Test/Verification |
|---|------|-------------------|-------------|-------------------|-------------------|
| 6 | **Semantic wrappers for summary cards & panels** | `resources/js/Pages/Dashboard/Index.vue` (summary‑grid, dashboard‑grid) | Wrap each `.summary-card` in `<section aria-label="…">` or `<article>`; add `<h2>` where appropriate. | 0.75 | aXe heading order check. |
| 7 | **Responsive grid utilities** | `Index.vue` (summary‑grid, dashboard‑grid) | Apply Tailwind `grid` classes (`grid-cols-1 md:grid-cols-2 lg:grid-cols-4` for summary, `md:grid-cols-2 lg:grid-cols-3` for panels). | 0.5 | Visual on different breakpoints. |
| 8 | **Design tokens / Tailwind config** | `tailwind.config.js` (create if missing) | Define `theme.extend.colors` (primary, danger, muted, success, warning) and spacing scale. Switch component classes to use `@apply` or `text-primary` etc. | 1.0 | Ensure build passes; visual regression. |
| 9 | **Lazy‑load rarely‑used pages** | `resources/js/app.js` (resolvePageComponent) | Use dynamic `import()` for pages like Reports, Suppliers, Purchases – already uses `import.meta.glob`, just verify chunk splitting via Vite. | 0.5 | Bundle size check (`vite build --report`). |
|10| **Move currency formatting to computed / filter** | `resources/js/Pages/Dashboard/Index.vue` | Replace inline `formatCurrency(point.total)` with a computed map or global filter to reduce re‑renders. | 0.5 | Performance profiling (Vue devtools). |

---

## 📌 Low‑Priority (nice‑to‑have, future‑proofing)
| # | Task | Files / Components | Description | Effort (dev‑days) |
|---|------|-------------------|-------------|-------------------|
|11| **Add `aria‑label` to badge when conveying status** | `VBadge.vue` | If badge indicates “Stok rendah”, expose via `aria-label="low stock"`. | 0.25 |
|12| **Introduce focus styles for custom buttons** | All UI button components (`VButton.vue`, etc.) | Ensure `focus-visible:ring-2` Tailwind utilities are present. | 0.25 |
|13| **Keyboard‑friendly chart navigation** | Dashboard chart list | Add `tabindex="0"` to each bar item and expose `aria-label` with value and date. | 0.5 |
|14| **Add unit tests for UI components** | `tests/Feature/Ui/*` (or Pest) | Verify `VBadge` renders correct variant class, `AppLayout` shows flash messages correctly. | 1.0 |

---

## 📅 Timeline (approx.)
| Sprint | Tasks (high → low) |
|--------|-------------------|
| **Sprint 1 (2 weeks)** | 1‑5 (all high‑priority) |
| **Sprint 2 (1 week)** | 6‑9 (medium‑priority) |
| **Sprint 3 (1 week)** | 10‑14 (low‑priority & cleanup) |

---

## 📂 File Links (for quick navigation)
- **Layout**: [`AppLayout.vue`](file:///Users/amrishf/Programming/laravel/inventory/resources/js/Components/Layouts/AppLayout.vue)
- **Dashboard page**: [`Dashboard/Index.vue`](file:///Users/amrishf/Programming/laravel/inventory/resources/js/Pages/Dashboard/Index.vue)
- **Badge component**: [`VBadge.vue`](file:///Users/amrishf/Programming/laravel/inventory/resources/js/Components/ui/VBadge.vue)
- **Main Blade template**: [`app.blade.php`](file:///Users/amrishf/Programming/laravel/inventory/resources/views/app.blade.php)

---

**Next steps**: Review this plan with the team, prioritize any additional UI/UX concerns, and start implementing Sprint 1 tasks. Feel free to ask for more detailed implementation specs for any item.
