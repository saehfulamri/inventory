# Roadmap Perbaikan Lanjutan

Dokumen ini menjadi backlog jangka menengah setelah Phase 12 (migrasi Inertia)
dan Phase 13 (Master Data UI — Kategori & Satuan) selesai. Nomor phase
mengikuti kelanjutan `07-task-backlog.md`.

# Phase 14 – Performance & Optimization

## Tujuan
- Memastikan aplikasi tetap responsif pada beban tinggi.
- Mengurangi waktu response API & halaman.
- Meminimalkan beban database melalui caching dan query optimization.

## Daftar Pekerjaan (Task Backlog)
| No | Task | Deskripsi | Prioritas |
|---|------|-----------|-----------|
| 14‑001 | Analisis N+1 queries | Gunakan Laravel Debugbar / Telescope untuk menemukan query berulang pada Dashboard, Produk, dan Penjualan. | P1 |
| 14‑002 | Optimasi query Dashboard | Refactor `DashboardService` untuk eager‑load relasi (`with(['sales', 'lowStockProducts'])`). | P1 |
| 14‑003 | Implementasi cache pada laporan | Cache hasil report harian/mingguan dengan `Cache::remember` (TTL 5 menit). | P2 |
| 14‑004 | Queue jobs untuk proses berat | Pindahkan pembuatan `stock movement` dan `report generation` ke job yang diproses via `redis` queue. | P2 |
| 14‑005 | Rate limiting API | Tambahkan middleware `throttle:60,1` pada route API untuk mencegah abuse. | P2 |
| 14‑006 | Profiling front‑end | Gunakan Chrome Lighthouse untuk mengukur FCP, LCP, dan CLS; perbaiki aset yang belum dimanfaatkan. | P3 |
| 14‑007 | Dokumentasi performance | Update `09-design.md` dengan guidelines performance & audit checklist. | P3 |

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
9. **Deploy**: setelah tag rilis dibuat, jalankan pipeline deploy (jika ada).

---

# Phase 15 – Accessibility & Internationalisation (i18n)

## Tujuan
- Memenuhi WCAG 2.2 AA untuk semua halaman.
- Menyediakan dukungan bahasa Indonesia & English.

## Task List
| No | Task | Detail |
|---|------|--------|
| 15‑001 | Audit aksesibilitas UI | Gunakan axe‑core atau Lighthouse CI, catat error ARIA. |
| 15‑002 | Tambah `lang` attribute & teks alternatif pada gambar. |
| 15‑003 | Implementasi paket `spatie/laravel-translatable` untuk model `Product`. |
| 15‑004 | Buat file bahasa (`resources/lang/id/*.php`, `resources/lang/en/*.php`). |
| 15‑005 | Update komponent UI (Button, Input) untuk menampilkan label aksesibel. |
| 15‑006 | Tambah test end‑to‑end (Laravel Dusk) untuk verifikasi tab order. |

## Workflow (GitHub Flow)
Sama seperti fase 14 – satu branch per fitur (`feat/a11y-i18n`), PR, CI, pint, merge.

---

# Phase 16 – Monitoring & Observability

## Tujuan
- Memantau kesehatan aplikasi di produksi.
- Memudahkan debugging produksi.

## Tasks
| No | Task | Detail |
|---|------|--------|
| 16‑001 | Install Laravel Telescope (production‑safe config). |
| 16‑002 | Setup Sentry SDK untuk exception tracking. |
| 16‑003 | Konfigurasi health‑check endpoint (`/health`) yang mengembalikan status DB, cache, queue. |
| 16‑004 | Tambah metric collection via `prometheus/client_php` dan expose `/metrics`. |
| 16‑005 | Dokumentasi monitoring di `10-deployment.md`. |

## GitHub Flow
Branch `feat/monitoring`, PR, pastikan CI lulus, deploy tag rilis.

---

# Ringkasan Roadmap
| Phase | Fokus |
|------|-------|
| 13 | Master Data UI (Kategori & Satuan) — dikerjakan bersamaan, lihat `07-task-backlog.md` |
| 14 | Performance & Optimisation |
| 15 | Accessibility & i18n |
| 16 | Monitoring & Observability |
| 17+ | Fitur bisnis lanjutan (loyalty, multi‑cabang, API publik, dll.) |

> **Catatan**: Pastikan setiap fase mengikuti aturan di `CONTRIBUTING.md` – branch protection, CI, Pint, dan checklist PR.
