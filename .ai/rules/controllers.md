---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Thin controllers via FormRequest + Service
Controllers stay thin: validate via FormRequest, delegate business logic to the matching Service, return Inertia::render with props — no queries or business logic in controllers. Shared cross-page props (auth, flash, can.*) go through HandleInertiaRequests middleware, not per-controller. Filter boilerplate uses App\Support\RequestFilters instead of inline array_filter.
