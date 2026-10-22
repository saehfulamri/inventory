# Changelog — Sistem Inventori & Penjualan

Semua perubahan penting pada project dicatat di sini. Dokumen ini memuat
perubahan **terbaru** (pekerjaan aktif). Riwayat rilis historis (sebelum
2026-10-20) dipindahkan ke [`../CHANGELOG.md`](../CHANGELOG.md).

Format:

```text
## YYYY-MM-DD

### Added
- ...

### Changed
- ...

### Fixed
- ...

### Security
- ...

### Database
- ...

### Breaking Changes
- ...
```

## 2026-10-22

### Added
- Endpoint `GET /health` (`HealthController`, di luar grup middleware `web`
  sehingga tidak membuat sesi/cookie) untuk readiness monitoring: mengecek
  koneksi database, cache (`put`/`get`/`forget`), dan queue; mengembalikan
  `{status, checks}` dengan HTTP 200/503. Terpisah dari liveness bawaan
  `GET /up`. Dokumentasi di `10-deployment.md` bagian 7.1.
- Test `HealthCheckTest` (cek sukses, cek gagal → 503, tanpa auth/sesi) dan
  `PosRateLimitTest` (limit per-user + terisolasi antar user).
- Seksi "Guidelines Performance & Audit Checklist" di `09-design.md` (agregat
  SQL, anti N+1, pagination, filter server-side, query DB-agnostic, batas
  penggunaan cache, bundling frontend).

### Repository & Open Source
- `SECURITY.md`: kebijakan keamanan, versi yang didukung, dan alur pelaporan
  privat via GitHub Security Advisories.
- `CODE_OF_CONDUCT.md` (Contributor Covenant 2.1) + tautan di `README.md` dan
  `CONTRIBUTING.md`.
- `.github/dependabot.yml`: pembaruan otomatis mingguan untuk Composer, npm, dan
  GitHub Actions (dikelompokkan per production/development).
- `.github/CODEOWNERS` (default `@saehfulamri`) dan contact link keamanan pada
  `.github/ISSUE_TEMPLATE/config.yml`.
- `composer.json`: deskripsi, keywords, `homepage`, `authors`, dan `support`
  diperbarui (sebelumnya masih metadata skeleton Laravel).
- `docs/screenshots/README.md`: panduan aset tangkapan layar.

### Changed
- `GET /sales/products` (endpoint JSON pencarian produk POS) kini dibatasi
  `throttle:60,1` per user untuk mencegah penyalahgunaan.
- Pembersihan dead code sisa Phase 14: method yang tidak pernah dipanggil
  dihapus dari contract + implementasi — `ProductRepositoryInterface::lowStockWithCategory()`
  (`EloquentProductRepository`), `SaleRepositoryInterface::todayWithItems()`
  (`EloquentSaleRepository`), dan `DashboardService::eagerLoadDashboard()`.
  Kontrak repository kini hanya memuat operasi yang benar-benar dipakai
  lapisan service/controller.
- Pemisahan changelog: entri historis (2026-09-21 s.d. 2026-09-29) dipindahkan
  ke `CHANGELOG.md` di root; `docs/08-changelog.md` kini hanya memuat perubahan
  terbaru. `README.md` menambahkan baris indeks untuk `CHANGELOG.md`.
- `EloquentSaleRepository::chartBetween()` kini benar-benar DB-agnostic:
  fungsi SQL `DATE()` (tidak tersedia di PostgreSQL) digantikan
  `groupBy('sale_date')` + normalisasi tanggal di PHP via `Carbon::parse()`.
  Perilaku identik di MySQL, SQLite, dan PostgreSQL — kolom `sale_date`
  dikembalikan berbeda bentuk per driver (`Y-m-d` vs `Y-m-d H:i:s`).

## 2026-10-21

### Added
- `.ai/rules/` — rules ter-commit untuk agent (index + services/controllers/js/tests)
  yang dirujuk `AGENTS.md`: larangan mutasi stok langsung, controller tipis,
  konvensi Vue/Inertia, dan Definition of Done.
- `docs/archive/README.md` menjelaskan alasan tiap dokumen diarsipkan.
- Peta Modul di `README.md` (alur data + tabel "mulai baca dari" per modul).
- Frontend utilitas bersama: `utils/filters.js` (`hasValue`/`buildParams`/
  `filterString`/`activeStatusOptions`) dan `utils/statuses.js`
  (`statusLabels`/`statusVariants`/`paymentMethodLabels`/`labelMapFrom`).

### Changed
- Higiene dokumentasi: 4 dokumen yang tidak lagi berlaku diarsipkan ke
  `docs/archive/` — `05-database.md` (migrasi di `database/migrations/` adalah
  source of truth), `06-coding-rules.md` (digantikan `AGENTS.md` +
  `CONTRIBUTING.md` + `04-architecture.md`; aturan frontend unik diserap ke
  `04-architecture.md`), `13-agy_uiux_improvement_plan.md` &
  `14-agy_UI_UX_Audit_Report.md` (artefak audit sekali jalan). Cross-reference
  di `README.md`, `CONTRIBUTING.md`, `07-task-backlog.md`, dan
  `10-deployment.md` diperbarui.
- `04-architecture.md`: struktur direktori disesuaikan dengan kondisi aktual
  (`app/Enums`, `Middleware`, `Support`, `Pages/Units`, `ErrorPage.vue`,
  komponen per modul) dan konvensi Inertia/Vue dilengkapi (larangan `v-html`
  tanpa sanitasi, WCAG 2.2 AA, batas state global).
- Refactor konsistensi frontend (12 halaman Index/Report): `applyFilters()`
  yang identik digantikan `buildParams()`; peta label status/metode yang
  disalin 7× dikonsolidasi ke `utils/statuses.js`; `Reports/Stock.vue` dan
  `Sales/Pos.vue` memakai `VSelect`/`VButton` (bukan elemen mentah);
  flash mentah di `Login.vue` & `Purchases/Create.vue` memakai `VFlash`;
  badge mentah di `Reports/Movements.vue` memakai `VBadge`.
- Hilangkan string literal yang tersisa: `Sales/Pos.vue` menerima
  `defaultPaymentMethod` dari `SaleController`; `Purchases/Show.vue` menerima
  `canFinalize` dari `PurchaseController`; literal status/movement/payment di
  test diganti `Enum::->value`.

## 2026-10-20

### Added
- CRUD Kategori & Satuan (FR-MASTER-001/002/003 [P0], TASK-116–120):
  route `categories.*` & `units.*`, `CategoryController` & `UnitController`,
  `CategoryPolicy` & `UnitPolicy` (Admin & Gudang; nonaktifkan, tanpa hapus
  fisik), FormRequest dengan pesan Bahasa Indonesia, halaman Vue
  `Categories/{Index,Create,Edit}` & `Units/{Index,Create,Edit}`, serta nav
  Kategori/Satuan pada desktop & mobile. Menutup gap di mana `CategoryService`
  & `UnitService` sudah ada tetapi belum punya lapisan HTTP/UI.
- Test baru: `CategoryManagementTest`, `CategoryAuthorizationTest`,
  `UnitManagementTest`, `UnitAuthorizationTest` (30 test).

### Changed
- Dokumen konsolidasi: status remediasi infrastruktur (Fase 3 CI & Fase 4
  queue sudah di-merge) digabungkan ke
  `docs/12-copilot_infrastructure-remediation-plan.md`; salinan duplikat di
  root dihapus. `13-improvement-phase.md` dipindahkan ke
  `docs/15-improvement-roadmap.md` (penomoran phase disesuaikan agar tidak
  bentrok dengan backlog). `GITHUB_FLOW.md` dihapus — isinya redundan dengan
  bagian "Alur Kerja Git" di `CONTRIBUTING.md`.
- Refactor `StockService` (TASK-123): `increase()`/`decrease()` yang kembar
  disatukan ke `applyChange()` bersigned; pembuatan stock movement kini
  melalui `StockMovementRepositoryInterface::create()` (menutup bypass
  repository, method `create` ditambahkan ke contract + implementasi).
- Refactor `ReportService` (TASK-124): `salesFilters()`/`purchaseFilters()`
  yang identik baris-ke-baris digabung menjadi `dateFilters()`;
  `productOptions()` memakai `ProductRepositoryInterface::productOptions()`
  (method baru di contract + `EloquentProductRepository`).
- Helper baru `App\Support\RequestFilters` (TASK-125) menggantikan boilerplate
  `array_filter([...], fn ($v) => $v !== null)` di seluruh controller
  (Product, Supplier, Category, Unit, Sale, Purchase, Inventory, Report).

### Fixed
- Build Ziggy di produksi/CI (TASK-122): `resources/js/ziggy.js` berisi
  `http://localhost:8000` hardcode dan tidak pernah diregenerasi — URL route
  di bundle produksi bisa salah. File tidak lagi di-commit (gitignored);
  `npm run build` dan `npm run dev` sekarang menjalankan
  `php artisan ziggy:generate` lebih dulu sehingga `APP_URL` lingkungan
  masing-masing yang dipakai. Urutan step CI disesuaikan (env disiapkan
  sebelum build).

### Database
- Tidak ada perubahan skema.

<!-- ENTRI SEBELUM 2026-10-20 SUDAH DIPINDAH KE ../CHANGELOG.md -->
