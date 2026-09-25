# Sistem Inventori & Penjualan (Mini Supermarket)

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Tests](https://github.com/saehfulamri/inventory/actions/workflows/tests.yml/badge.svg)](https://github.com/saehfulamri/inventory/actions/workflows/tests.yml)

Aplikasi web monolith Laravel untuk mengelola master produk, stok, pembelian/penerimaan barang, penjualan, pengguna, supplier, serta laporan operasional.

## Fitur Utama

- **Master Data** — produk, kategori, satuan, supplier (kelola: daftar + cari/filter, tambah, edit, nonaktifkan), pengguna dengan role
- **Inventory** — stok real-time, penerimaan barang, penyesuaian stok, riwayat pergerakan stok (audit trail), indikator stok minimum
- **Penjualan (POS)** — pencarian produk (nama/SKU/barcode), keranjang, perhitungan subtotal & kembalian, metode bayar cash/transfer/QRIS/card, struk siap-cetak
- **Dashboard** — ringkasan penjualan hari ini, chart 7 hari terakhir, produk stok menipis
- **Laporan** — penjualan, pembelian, stok, pergerakan stok + ekspor CSV
- **Keamanan & audit** — role-based access (Admin/Kasir/Gudang/Manager), semua mutasi stok tercatat dengan sumber transaksi, database transaction untuk operasi atomik

## Dokumentasi Proyek

Dokumen spesifikasi dan panduan pengembangan tersedia dalam bentuk file bernomor:

| File | Isi |
| --- | --- |
| `01-project-brief.md` | Ringkasan, tujuan, target pengguna, ruang lingkup MVP, teknologi. |
| `02-requirements.md` | Functional & non-functional requirements (prioritas P0/P1/P2). |
| `03-product-spec.md` | Spesifikasi UI/UX, user flow, acceptance criteria. |
| `04-architecture.md` | Arsitektur Laravel Service/Repository Pattern. |
| `05-database.md` | Desain database MySQL. |
| `06-coding-rules.md` | Coding rules & aturan pengembangan AI. |
| `07-task-backlog.md` | Task backlog dan roadmap. |
| `08-changelog.md` | Catatan perubahan penting proyek. |
| `09-design.md` | Sistem desain UI (token warna, tipografi, komponen) sebagai referensi implementasi frontend. |
| `10-deployment.md` | Panduan deployment ke produksi (env, migrate, cache, web server, update/rollback). |
| `11-backup-restore.md` | Prosedur backup & restore database dan storage, plus cron otomatis. |

## Arsitektur

- Laravel monolith + Vue.js 3 via Inertia.js (SPA) + MySQL.
- Controller tipis → Service (business logic) → Repository (data access) → Eloquent.
- Setiap perubahan stok tercatat sebagai stock movement dengan sumber yang jelas.
- Operasi multi-step (penjualan, penerimaan) menggunakan database transaction secara atomic.

## Prasyarat

- PHP >= 8.4
- Composer
- MySQL 8+ / 9+
- Node.js & npm (untuk membangun aset frontend Vue/Inertia)

## Instalasi (Development)

```sh
# 1. Install dependensi
composer install

# 2. Siapkan environment
cp .env.example .env
php artisan key:generate

# 3. Konfigurasi koneksi database di .env
# DB_CONNECTION=mysql
# DB_DATABASE=<nama database>
# DB_USERNAME=<user>
# DB_PASSWORD=<password>
# SESSION_DRIVER=database
# CACHE_STORE=database
# QUEUE_CONNECTION=database

# 4. Buat database dan jalankan migrasi
mysql -u root -p -e "CREATE DATABASE inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate

# 5. Symlink storage publik (diperlukan untuk fitur foto produk)
php artisan storage:link

# 6. Install & build aset frontend (Vue/Inertia) — saat development jalankan `npm run dev` untuk hot reload
npm install && npm run build

# 7. Jalankan server development
php artisan serve
```

Akses `http://localhost:8000/login` untuk masuk.

### Data Demo

Untuk data contoh (kategori, satuan, supplier, produk, dan akun pengguna 4 role), jalankan seeder:

```sh
php artisan db:seed
```

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@example.com` | `password` |
| Kasir | `kasir@example.com` | `password` |
| Petugas Gudang | `gudang@example.com` | `password` |
| Manager | `manager@example.com` | `password` |

> Seeder hanya berjalan pada environment `local`/`testing` dan menyediakan akun demo — jangan digunakan di produksi.

## Testing

Test menggunakan SQLite in-memory (dikonfigurasi di `phpunit.xml`), sehingga tidak memerlukan MySQL.

```sh
php artisan test
```

## Kontribusi

Panduan lengkap untuk berkontribusi (setup, arsitektur, konvensi coding, alur git, checklist PR, dsb.) tersedia di [CONTRIBUTING.md](CONTRIBUTING.md).

## Lisensi

Proyek ini dirilis di bawah lisensi [MIT](LICENSE).

Copyright (c) 2026 Saehful Amri.

## Status

MVP untuk fitur inti selesai (Phase 0–8) dan rilis `v0.1.0` sudah dibuat. Phase 9 (Authorization & Hardening) selesai — termasuk review keamanan (validasi, CSRF/XSS, logging, mass assignment) dan hardening tambahan (rate-limit login, proteksi CSV formula-injection). **Phase 10 (Quality & Release) selesai** — dokumentasi deployment (`10-deployment.md`) & backup/restore (`11-backup-restore.md`), dan fitur foto produk (TASK-096). Seluruh roadmap selesai. **Phase 11 — Modul Kelola Supplier (FR-SUP-001/004/005) ditambahkan** pasca-rilis `v0.2.0`: daftar + filter, tambah, edit, nonaktifkan (akses Admin & Gudang). Lihat `07-task-backlog.md` dan `08-changelog.md` untuk detail.