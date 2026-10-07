# Phase 13 – Performance & Optimization

## Tujuan
- Memastikan aplikasi tetap responsif pada beban tinggi.
- Mengurangi waktu response API & halaman.
- Meminimalkan beban database melalui caching dan query optimization.

## Daftar Pekerjaan (Task Backlog)
| No | Task | Deskripsi | Prioritas |
|---|------|-----------|-----------|
| 13‑001 | Analisis N+1 queries | Gunakan Laravel Debugbar / Telescope untuk menemukan query berulang pada Dashboard, Produk, dan Penjualan. | P1 |
| 13‑002 | Optimasi query Dashboard | Refactor `DashboardService` untuk eager‑load relasi (`with(['sales', 'lowStockProducts'])`). | P1 |
| 13‑003 | Implementasi cache pada laporan | Cache hasil report harian/mingguan dengan `Cache::remember` (TTL 5 menit). | P2 |
| 13‑04 | Queue jobs untuk proses berat | Pindahkan pembuatan `stock movement` dan `report generation` ke job yang diproses via `redis` queue. | P2 |
| 13‑005 | Rate limiting API | Tambahkan middleware `throttle:60,1` pada route API untuk mencegah abuse. | P2 |
| 13‑006 | Profiling front‑end | Gunakan Chrome Lighthouse untuk mengukur FCP, LCP, dan CLS; perbaiki aset yang belum dimanfaatkan. | P3 |
| 13‑007 | Dokumentasi performance | Update `09-design.md` dengan guidelines performance & audit checklist. | P3 |

## Langkah Implementasi (GitHub Flow)
1. **Buat branch**: `feat/performance-optimisation`.
2. **Setup environment**: Pastikan `APP_ENV=local` dan Redis berjalan (`docker compose up -d redis`).
3. **Tambah paket** (jika belum ada):
   ```bash
   composer require laravel/telescope --dev
   composer require predis/predis
   npm install --save-dev lighthouse
   ```
4. **Implementasi**: lakukan satu task per commit, ikuti Conventional Commits (`feat: eager‑load dashboard relations`).
5. **Tes**:
   - Unit/Feature test untuk memastikan data yang di‑cache tetap konsisten.
   - Jalankan `php artisan test --filter=DashboardTest`.
   - Pastikan CI (`.github/workflows/tests.yml`) lulus pada PHP 8.4 & 8.5.
6. **Lint & format**: `vendor/bin/pint`.
7. **Push & PR**: gunakan template PR; centang checklist (tests, pint, documentation).
8. **Review & Merge**: squash‑and‑merge ke `main`.
9. **Deploy**: setelah tag `v0.3.0` dibuat, jalankan pipeline deploy (jika ada).

---

# Phase 14 – Accessibility & Internationalisation (i18n)

## Tujuan
- Memenuhi WCAG 2.2 AA untuk semua halaman.
- Menyediakan dukungan bahasa Indonesia & English.

## Task List
| No | Task | Detail |
|---|------|--------|
| 14‑001 | Audit aksesibilitas UI | Gunakan axe‑core atau Lighthouse CI, catat error ARIA.
| 14‑002 | Tambah `lang` attribute & teks alternatif pada gambar.
| 14‑003 | Implementasi paket `spatie/laravel-translatable` untuk model `Product`.
| 14‑004 | Buat file bahasa (`resources/lang/id/*.php`, `resources/lang/en/*.php`).
| 14‑005 | Update komponent UI (Button, Input) untuk menampilkan label aksesibel.
| 14‑006 | Tambah test end‑to‑end (Laravel Dusk) untuk verifikasi tab order.

## Workflow (GitHub Flow)
Sama seperti fase 13 – satu branch per fitur (`feat/a11y-i18n`), PR, CI, pint, merge.

---

# Phase 15 – Monitoring & Observability

## Tujuan
- Memantau kesehatan aplikasi di produksi.
- Memudahkan debugging produksi.

## Tasks
| No | Task | Detail |
|---|------|--------|
| 15‑001 | Install Laravel Telescope (production‑safe config). |
| 15‑002 | Setup Sentry SDK untuk exception tracking. |
| 15‑003 | Konfigurasi health‑check endpoint (`/health`) yang mengembalikan status DB, cache, queue. |
| 15‑004 | Tambah metric collection via `prometheus/client_php` dan expose `/metrics`. |
| 15‑005 | Dokumentasi monitoring di `10-deployment.md`. |

## GitHub Flow
Branch `feat/monitoring`, PR, pastikan CI lulus, deploy tag `v0.4.0`.

---

# Ringkasan Roadmap
| Phase | Fokus |
|------|-------|
| 13 | Performance & Optimisation |
| 14 | Accessibility & i18n |
| 15 | Monitoring & Observability |
| 16+ | Fitur bisnis lanjutan (loyalty, multi‑cabang, API publik, dll.) |

> **Catatan**: Pastikan setiap fase mengikuti aturan di `CONTRIBUTING.md` – branch protection, CI, Pint, dan checklist PR.
