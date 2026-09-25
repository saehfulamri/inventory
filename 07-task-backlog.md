# Task Backlog — Sistem Inventori & Penjualan

## Status

- [ ] Todo
- [~] In Progress
- [x] Done
- [-] Cancelled

## Phase 0 — Project Foundation

- [x] TASK-001 Buat repository Git
- [x] TASK-002 Buat project Laravel
- [x] TASK-003 Konfigurasi `.env` development
- [x] TASK-004 Konfigurasi MySQL
- [x] TASK-005 Buat struktur dokumentasi
- [x] TASK-006 Setup authentication
- [x] TASK-007 Setup base layout Blade
- [x] TASK-008 Setup error handling/logging dasar

## Phase 1 — Database & Master Data

- [x] TASK-009 Migration users/role
- [x] TASK-010 Migration categories
- [x] TASK-011 Migration units
- [x] TASK-012 Migration suppliers
- [x] TASK-013 Migration products
- [x] TASK-014 Migration purchases/purchase_items
- [x] TASK-015 Migration sales/sale_items
- [x] TASK-016 Migration stock_movements
- [x] TASK-017 Migration stock_adjustments
- [x] TASK-018 Factory dan seeder development
- [x] TASK-019 Product model + relationships
- [x] TASK-020 Category model + relationships
- [x] TASK-021 Unit model + relationships
- [x] TASK-022 Supplier model + relationships

## Phase 2 — Repository & Service Foundation

- [x] TASK-023 Repository contracts
- [x] TASK-024 Eloquent repositories
- [x] TASK-025 ProductService
- [x] TASK-026 CategoryService
- [x] TASK-027 UnitService
- [x] TASK-028 SupplierService
- [x] TASK-029 Service Provider bindings
- [x] TASK-030 Base testing setup

## Phase 3 — Product Management

- [x] TASK-031 Product list
- [x] TASK-032 Product search/filter
- [x] TASK-033 Product create
- [x] TASK-034 Product validation
- [x] TASK-035 Product edit
- [x] TASK-036 Product deactivate
- [x] TASK-037 Product authorization tests

## Phase 4 — Inventory Receiving

- [x] TASK-038 Purchase/Receiving Service
- [x] TASK-039 Purchase repository
- [x] TASK-040 Purchase item repository
- [x] TASK-041 Receiving form
- [x] TASK-042 Add receiving items
- [x] TASK-043 Finalize receiving
- [x] TASK-044 Increase stock atomically
- [x] TASK-045 Create PURCHASE_IN stock movement
- [x] TASK-046 Receiving feature tests

## Phase 5 — Stock Management

- [x] TASK-047 Stock dashboard/list
- [x] TASK-048 Low-stock filter
- [x] TASK-049 Stock movement repository
- [x] TASK-050 Stock movement history
- [x] TASK-051 Stock adjustment service
- [x] TASK-052 Stock adjustment UI
- [x] TASK-053 Stock adjustment tests

## Phase 6 — Sales

- [x] TASK-054 Sale repository
- [x] TASK-055 Sale item repository
- [x] TASK-056 SaleService
- [x] TASK-057 Product search for cashier
- [x] TASK-058 Cart UI
- [x] TASK-059 Quantity management
- [x] TASK-060 Sale calculation
- [x] TASK-061 Payment form
- [x] TASK-062 Stock validation
- [x] TASK-063 Complete sale transaction
- [x] TASK-064 Decrease stock atomically
- [x] TASK-065 Create SALE_OUT movement
- [x] TASK-066 Cash payment/change calculation
- [x] TASK-067 Sale tests
- [x] TASK-068 Receipt/print view

## Phase 7 — Dashboard

- [x] TASK-069 Dashboard summary cards
- [x] TASK-070 Today's sales summary
- [x] TASK-071 Low-stock products
- [x] TASK-072 Active product count
- [x] TASK-073 Sales chart [P1]

## Phase 8 — Reports

- [x] TASK-074 Sales report
- [x] TASK-075 Stock report
- [x] TASK-076 Stock movement report
- [x] TASK-077 Purchase/receiving report
- [x] TASK-078 Export report [P1]

## Phase 9 — Authorization & Hardening

- [x] TASK-079 Define role permissions
- [x] TASK-080 Apply policies/middleware
- [x] TASK-081 Authorization feature tests
- [x] TASK-082 Review validation
- [x] TASK-083 Review CSRF/XSS/security configuration
- [x] TASK-084 Review sensitive logging
- [x] TASK-085 Review mass assignment

## Phase 10 — Quality & Release

- [x] TASK-086 Run full automated test suite
- [x] TASK-087 Fix regression issues
- [x] TASK-088 Database migration review
- [x] TASK-089 Seed production-safe defaults
- [x] TASK-090 Environment/config review
- [x] TASK-091 Deployment preparation
- [x] TASK-092 Backup/restore procedure
- [x] TASK-093 Update README
- [x] TASK-094 Update changelog
- [x] TASK-095 MVP release
- [x] TASK-096 Foto produk [P1 — opsional]: migrasi `image_path` pada `products`; upload + validasi tipe/ukuran + thumbnail; tampilkan di list/detail produk dan kart belanja kasir. Alternatif hemat biaya: simpan URL gambar (tanpa upload) jika upload dianggap terlalu berat. *(Diimplementasikan: upload JPG/PNG/WEBP ≤ 2MB, thumbnail tampilan via CSS `object-fit`, tampil di list produk & keranjang POS. Foto lama otomatis dihapus saat diganti.)*

## Phase 11 — Supplier Management

- [x] TASK-097 Halaman daftar supplier: filter kata kunci (nama/kode) & status, pagination, kolom Kontak/Alamat/Status/Aksi, aksi nonaktifkan (soft-disable, histori penerimaan tetap)
- [x] TASK-098 Form tambah/edit supplier (`suppliers/create`, `suppliers/edit`, `_form`) — kode opsional unik, nama wajib, email valid, checkbox status
- [x] TASK-099 Validasi `StoreSupplierRequest`/`UpdateSupplierRequest` dengan pesan Bahasa Indonesia
- [x] TASK-100 `SupplierPolicy` (viewAny/create/update/delete: Admin & Gudang) + route `suppliers.*` + `SupplierController` + link nav "Supplier"
- [x] TASK-101 Tests: `SupplierManagementTest` (list, empty state, create, validasi, update, nonaktifkan, filter) & `SupplierAuthorizationTest` (policy & HTTP 403)
- [x] TASK-102 Update dokumentasi: `02-requirements.md` (FR-SUP-004/005/006), `07-task-backlog.md`, `08-changelog.md`, `README.md`

## Phase 12 — Frontend Migration ke Inertia.js + Vue 3

- [x] TASK-103 Install & konfigurasi fondasi Inertia + Vue: `inertiajs/inertia-laravel`, `@inertiajs/vue3`, `vue`, `@vitejs/plugin-vue`, Ziggy; root template `app.blade.php`; middleware `HandleInertiaRequests` (shared props auth + flash); plugin Vue di `vite.config.js`
- [x] TASK-104 Foundation UI: `AppLayout.vue` (topbar/nav berbasis role, flash message, skip-link, focus management) + komponen base (button, card, form-field, table, pagination, empty-state, confirmation dialog) mengikuti token `09-design.md`
- [x] TASK-105 Auth & Error: halaman login (Inertia) + root component Error (403/404/500)
- [x] TASK-106 Dashboard page
- [x] TASK-107 Produk: list (filter/pagination) + form create/edit (termasuk upload foto produk via `useForm` multipart)
- [x] TASK-108 Supplier: list (filter) + form create/edit
- [x] TASK-109 Inventory: stok list, riwayat movement, form adjustment
- [x] TASK-110 Penerimaan: index, create (baris item dinamis), show, finalize
- [x] TASK-111 Penjualan: index, POS interaktif (cart reactive, pencarian produk via endpoint JSON yang sudah ada), show/struk (print)
- [x] TASK-112 Laporan: index + laporan sales/stock/movements/purchases + link ekspor CSV
- [x] TASK-113 Update feature tests ke assertion Inertia (`assertInertia`); hapus view Blade lama; verifikasi `npm run build`
- [x] TASK-114 Hardening & security: hilangkan `'unsafe-inline'` pada `script-src` CSP, tinjau CSRF/XSRF Inertia, pertahankan NFR-009/NFR-010 — solusi akhir: izinkan origin Vite dev server saat `npm run dev` (dari `public/hot`) + **CSP nonce per-respons** sebagai defense-in-depth untuk data-block Inertia; tanpa keduanya halaman beku/putih saat review; lihat changelog untuk detail
- [x] TASK-115 Full regression suite + update dokumentasi (changelog, README)

Catatan konvensi: migrasi inkremental per modul (satu task pada satu waktu, sesuai AI Task Protocol), memanfaatkan kemampuan Inertia berjalan berdampingan dengan Blade selama transisi, dan business logic tetap server-side (Controller → Service → Repository tidak berubah).

## AI Task Protocol

AI hanya mengerjakan task yang diminta.

Contoh:

```text
Kerjakan TASK-063.

Sebelum coding:
- baca architecture.md
- baca database.md
- baca coding-rules.md
- inspeksi implementation SaleService yang ada

Jangan mengerjakan task lain.
Setelah selesai:
- jalankan test relevan
- laporkan file berubah
- laporkan test dan hasilnya
```

## Task Dependency Principle

Jangan mengerjakan task yang dependensinya belum tersedia.

Contoh:

```text
Migration
   ↓
Model
   ↓
Repository
   ↓
Service
   ↓
Controller
   ↓
Vue (Inertia Page)
   ↓
Test
```

Namun test dapat dibuat lebih awal jika pendekatan TDD digunakan.
