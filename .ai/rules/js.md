---
paths:
  - 'resources/js/**'
---

# Js

## Vue/Inertia frontend conventions
Business calculations (stock, subtotal, grand total, change) stay server-side in Services; Vue only presents and does light interaction. Navigate with Inertia Link/router + Ziggy named routes, never location.href or raw fetch for page changes. Forms use useForm from @inertiajs/vue3. Never render user content with v-html without sanitization. Reuse base components in Components/ui (VButton, VTable, VFormField, etc.) instead of duplicating markup. Keep WCAG 2.2 AA: programmatic labels, focus indicators, aria-live for flash messages. No heavy global state — shared props via HandleInertiaRequests, local state inside components.
