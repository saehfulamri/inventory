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

- [ ] TASK-079 Define role permissions
- [ ] TASK-080 Apply policies/middleware
- [ ] TASK-081 Authorization feature tests
- [ ] TASK-082 Review validation
- [ ] TASK-083 Review CSRF/XSS/security configuration
- [ ] TASK-084 Review sensitive logging
- [ ] TASK-085 Review mass assignment

## Phase 10 — Quality & Release

- [ ] TASK-086 Run full automated test suite
- [ ] TASK-087 Fix regression issues
- [ ] TASK-088 Database migration review
- [ ] TASK-089 Seed production-safe defaults
- [ ] TASK-090 Environment/config review
- [ ] TASK-091 Deployment preparation
- [ ] TASK-092 Backup/restore procedure
- [ ] TASK-093 Update README
- [ ] TASK-094 Update changelog
- [ ] TASK-095 MVP release
- [ ] TASK-096 Foto produk [P1 — opsional]: migrasi `image_path` pada `products`; upload + validasi tipe/ukuran + thumbnail; tampilkan di list/detail produk dan kart belanja kasir. Alternatif hemat biaya: simpan URL gambar (tanpa upload) jika upload dianggap terlalu berat.

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
Blade
   ↓
Test
```

Namun test dapat dibuat lebih awal jika pendekatan TDD digunakan.
