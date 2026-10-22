# Rules Index

Peta glob ke file rules. Baca semua rule file yang glob-nya mencakup path yang
Anda sentuh sebelum edit.

| Glob | Rules |
| --- | --- |
| `app/Services/**` | [services.md](services.md) — stock mutations hanya via StockService, transaksi multi-step |
| `app/Http/Controllers/**` | [controllers.md](controllers.md) — controller tipis (FormRequest + Service + Inertia props) |
| `resources/js/**` | [js.md](js.md) — konvensi Vue/Inertia (server-side logic, useForm, V* components, a11y) |
| `tests/**` | [tests.md](tests.md) — Definition of Done, Pint, test relevan |

Aturan framework/package (Laravel, Pint, PHPUnit, deployment) ada di
`AGENTS.md`. Panduan kontribusi manusia: `CONTRIBUTING.md`.
