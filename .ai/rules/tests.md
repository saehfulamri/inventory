---
paths:
  - 'tests/**'
---

# Tests

## Definition of Done for every task
A task is done only when: requirement met, architecture unchanged (no swapping patterns without explicit decision), FormRequest validation present, authorization (policy/gate) present where relevant, relevant tests pass (run `php artisan test --filter=...`), no unrelated file changes, and docs/changelog updated when behavior changes. Run `vendor/bin/pint --dirty` after PHP edits.
