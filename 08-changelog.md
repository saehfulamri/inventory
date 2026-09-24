# Changelog — Sistem Inventori & Penjualan

Semua perubahan penting pada project dicatat di sini.

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

## 2026-09-24

### Added
- Persiapan open source: lisensi MIT (`LICENSE`), workflow GitHub Actions (`.github/workflows/tests.yml` — Pint + PHPUnit di PHP 8.3 & 8.4, memakai SQLite in-memory tanpa service MySQL), badge lisensi/PHP/Laravel di README, dan daftar fitur utama.

### Changed
- `composer.json`: `name` diubah dari `laravel/laravel` → `amrishf/sistem-inventori`.
- `README.md`: lisensi diubah dari privat/internal → MIT; tambah bagian "Fitur Utama", "Data Demo" (seeder + akun 4 role), "Lisensi", dan "Status" yang diperbarui.
- `08-changelog.md`: koreksi klaim laporan — laporan penjualan hanya filter tanggal (bukan status/metode bayar), laporan stok tanpa indikator "expired" (fitur tidak ada), laporan pembelian tanpa filter supplier (belum di-wire), wording "sidebar" → "topbar", dan hapus duplikasi seksi Architecture.
- Pint: perbaikan style 6 file; import tidak terpakai (`CategoryRepositoryInterface as CatInterfaceAlias`) di `AppServiceProvider` dihapus.

### Fixed
- `resources/views/reports/purchases.blade.php`: kolom Total memakai `$purchase->total_amount` (sebelumnya `$purchase->grand_total` yang tidak ada pada model Purchase → selalu tampil Rp 0,00). Assertion nilai total ditambahkan di `ReportFeatureTest`.

## 2026-09-23

### Changed
- `public/css/app.css`: `.card-grid` kini memakai `margin-block: 16px` sebagai jarak antar-seksi (spacing basis 8px × 2). Inline `style="padding: 1rem 0px"` di `reports/index.blade.php` dihapus dan dipindah ke stylesheet (design system terpusat, dapat dioverride di breakpoint/responsive).

## 2026-09-22

### Added
- Fitur Laporan (Phase 8): halaman index report (ringkasan penjualan/pembelian/stok), laporan penjualan dengan filter periode (tanggal dari-sampai), laporan stok (pencarian produk, indikator stok di bawah minimum), laporan stok movement (filter produk & tipe, total per tipe), laporan pembelian (filter periode tanggal), dan ekspor CSV penjualan (`GET /reports/sales/export`) yang streaming.
- `ReportService` — agregasi ringkasan + paginasi semua sumber (sales/purchases/stock/movements) dan generator CSV streaming per-halaman, dapat diakses Admin & Manager lewat `Gate::viewReports`.
- `ReportController` + route `reports.*` + 5 view Blade (`reports/index|sales|stock|movements|purchases`); menu "Laporan" di topbar di-gate `@can('viewReports')`.
- Tracer bobot: `EloquentStockMovementRepository::typeTotals()` (key enum `->value`), `EloquentPurchaseRepository::summaryBetween()` (enum `PurchaseStatus`), `AuthorizesRequests` trait di `ReportController`.

### Changed
- `eloquent-stock-movement-repository` `mapWithKeys`: key per tipe kini memakai `movement_type->value` (sebelumnya enum objek → TypeError).
- Poles tampilan halaman laporan: tambah definisi `.card-grid` & `.report-card*` di `public/css/app.css` (sebelumnya blade laporan memakainya tapi tidak ada styling sama sekali → kartu index render datar tanpa kartu); gap seluruh grid laporan (`card-grid`, `summary-grid`) distandarkan ke `16px`; halaman `reports/movements` dikonfirmasi sebagai tabel-detail-murni (tidak pakai kartu ringkasan) sehingga konsisten dengan sifat halamannya.

### Fixed
- `ReportController` kini memakai `AuthorizesRequests` sehingga metode `authorize('viewReports')` berfungsi (sebelumnya 500 untuk semua user di tiap halaman laporan).
- `EloquentPurchaseRepository`: nambah `use App\Enums\PurchaseStatus;` (sebelumnya kelas tidak ditemukan → 500 di summary pembelian).
- `EloquentStockMovementRepository::typeTotals`: enum dipakai sebagai key array (offset error) → diganti `->value`.
- `Request` typo `$requestprop` di `exportSales()`.

## 2026-09-21

### Added
- Foundation: repositori Git, project Laravel 13, konfigurasi `.env` dan database MySQL `inventory`.
- README proyek dengan panduan instalasi dan indeks dokumentasi.
- Authentication dasar: login, logout, halaman dashboard; test feature login/logout.
- Base layout Blade (`layouts/app.blade.php`) dengan flash message dan stylesheet dasar.
- Error handling dan logging sesuai default Laravel (stack/single log).
- Project documentation foundation.

## 2026-09-21

### Added
- Database & master data: migrasi `users.role`, `categories`, `units`, `suppliers`, `products`, `purchases`/`purchase_items`, `sales`/`sale_items`, `stock_movements`, `stock_adjustments` (mysql, InnoDB, money `decimal(15,2)`, qty/stok `decimal(15,3)`).
- Enums domain: `Role`, `SaleStatus`, `PurchaseStatus`, `AdjustmentStatus`, `MovementType`, `PaymentMethod`.
- Model + relasi: `Product`, `Category`, `Unit`, `Supplier`, `Purchase`, `PurchaseItem`, `Sale`, `SaleItem`, `StockMovement`, `StockAdjustment`.
- Factory: `CategoryFactory`, `UnitFactory`, `SupplierFactory`, `ProductFactory`; `UserFactory` kini berisi `role` default.
- `DevelopmentSeeder` (demo 4 role user, kategori, satuan, supplier, produk) — hanya berjalan di env `local`/`testing`.
- Test: skema database, relasi model, keunikan SKU, seeder.

### Architecture
- Repository Layer: contract (`app/Repositories/Contracts`) + implementasi Eloquent (`app/Repositories/Eloquent`) untuk Product, Category, Unit, Supplier.
- Service Layer: `ProductService`, `CategoryService`, `UnitService`, `SupplierService` (orchestrasi, aturan deaktivasi master data).
- Binding interface → implementasi Eloquent di `AppServiceProvider`.
- Test: verifikasi wiring container, operasi service, dan filter server-side (paginate, keyword, status aktif).
- Project brief.
- Functional and non-functional requirements.
- Product specification.
- Laravel Service/Repository architecture definition.
- MySQL database design.
- Coding and AI development rules.
- Initial task backlog.

### Notes
Dokumen ini menjadi catatan perubahan project setelah implementasi dimulai.

Setiap perubahan penting terhadap:
- architecture,
- database,
- business rule,
- security,
- public behavior,
- API/interface internal,

harus diperbarui di changelog.

## 2026-09-21

### Added
- Phase 4 — Inventory Receiving: repository Purchase & PurchaseItem (contract + Eloquent) dengan binding di `AppServiceProvider`.
- `PurchaseService`: buat penerimaan draft (nomor unik `PO-YYYYMMDD-NNNN`, header + items, perhitungan subtotal/total) dan finalisasi dalam satu database transaction.
- `StockService::increase`: mekanisme terpusat untuk menambah stok (row lock via repository) dan mencatat `stock_movements` dengan referensi morfable ke sumber transaksi.
- Finalisasi penerimaan menambah stok produk secara atomik dan membuat stock movement `PURCHASE_IN` (`reference_type` = `App\Models\Purchase`).
- `StorePurchaseRequest` (validasi supplier, tanggal, minimal satu item, qty/harga ≥ 0), `PurchasePolicy` (Admin & Gudang).
- `PurchaseController` + routes `purchases.index/create/store/show/finalize`; nav "Penerimaan" di layout.
- Views: daftar penerimaan (filter keyword/supplier/status + pagination), form penerimaan (row dinamis berbasis `<template>` + JS, tanpa dependency baru), halaman detail dengan tombol finalisasi (konfirmasi).
- CSS: badge warning, section title, detail list, style input di dalam tabel.
- Factories `PurchaseFactory` & `PurchaseItemFactory`; model `Purchase`/`PurchaseItem` kini `HasFactory`.
- Tests: `PurchaseServiceTest` (draft, nomor unik, finalize + movement, tolak finalize ganda, atomic rollback), `PurchaseReceivingTest` (flow HTTP + validasi), `PurchaseAuthorizationTest` (policy + 403 role non-izin), binding container repository/service.

### Changed
- `ProductRepositoryInterface`/`EloquentProductRepository`/`ProductService`: tambah `findByIdForUpdate()` (row lock) dan `findActive()`.
- Layout `resources/views/layouts/app.blade.php`: tambah `@stack('scripts')`.

### Notes
- Finalisasi penerimaan yang sudah `completed` ditolak: `ValidationException` dengan pesan user-friendly, stok tidak berubah.
- Backup flow stok (sale `SALE_OUT`, adjustment, dll.) akan dikerjakan pada Phase 5/6.

## 2026-09-21

### Changed
- `03-product-spec.md`: tambah aksesibilitas WCAG 2.2 level AA sebagai prinsip UX (kontras, operasi keyboard, label programatik, identifikasi error, resize 200%, bahasa & semantic HTML). Ditambahkan juga sebagai acceptance criteria umum fitur.
- `02-requirements.md`: tambah NFR-009 Accessibility (WCAG 2.2 AA) dan NFR-010 Document Language sebagai requirement [P0].

## 2026-09-21

### Added
- `09-design.md` (sebelumnya `DESIGN.md` di root): sistem desain UI ala Apple — token warna (Action Blue `#0066cc` sebagai satu-satunya aksen interaktif), topografi (SF Pro/system-ui, body 17px, weight 600), bentuk/radius (pill untuk aksi, `sm`/`lg` untuk kartu & utility), elevation (tanpa shadow dekoratif, hairline sebagai pembatas), spacing basis 8px, breakpoints & touch target 44×44px. Terdaftar di indeks README sebagai dokumen ke-9.

### Changed
- `public/css/app.css` ditulis ulang mengikuti token `09-design.md`:
  - Global nav hitam ultra-tipis (44px) dengan tombol `Keluar` sebagai dark-utility (`btn--nav`); aksi destruktif (`btn--danger`) dibuat langka dengan teks merah `#c93838` (kenaikan bertentangan dengan aksen tunggal hanya untuk destruktif, mengikuti pola platform).
  - Tombol: primary pill Action Blue (`scale(0.95)` saat aktif), secondary pearl capsule (`#fafafc`), varian kompak `btn--sm` untuk aksi baris tabel.
  - Kartu putih hairline radius 18px (auth, detail, tabel, filter) di atas canvas parchment `#f5f5f7`; input tinggi 44px; keyword filter memakai grammar pill "search".
  - Page title 28px/600 dengan negative letter-spacing; body 17px/1.44.
  - Typography/responsive: `.muted` memakai ink-muted-80 `#333333` agar kontras tetap ≥ 4.5:1 di atas parchment (WCAG AA dipertahankan).
- Views `layouts/app.blade.php`, `products/index.blade.php`, `purchases/index.blade.php`: pemakaian class button mengikuti grammar baru (`btn--nav`, `btn--sm`).

## 2026-09-21

### Fixed
- `public/css/app.css`: `--color-muted` digelapkan `#6b7280` → `#4b5563` agar kontras teks terhadap background `#f3f4f6` ≥ 4.5:1 (WCAG 2.2 AA, SC 1.4.3).
- Form Penerimaan (`purchases/create`): select/input pada baris item (statis & template dinamis) diberi `aria-label` ("Pilih produk"/"Jumlah"/"Harga beli") agar punya accessible name (SC 1.3.1/4.1.2).
- `purchases/create`: flash error items diberi `role="alert"`; `auth/login`: kotak error diberi `role="alert"`; flash success di layout diberi `role="status"` (SC 4.1.3 Status Messages).
- Layout: tambah skip link "Lewati ke konten utama" + `id="main"` (SC 2.4.1 Bypass Blocks) dan `:focus-visible` global untuk `a`/`button`/`input`/`select` (SC 2.4.7 Focus Visible).

## 2026-09-21

### Added
- Phase 5 — Stock Management: halaman Stok (`GET /inventory`, route `inventory.index`) dengan daftar produk (SKU, nama, kategori, satuan, stok, stok minimum, status "Stok menipis"/"Aman") dan filter keyword/kategori/**hanya stok menipis** (TASK-047/048).
- Repository stok: `StockMovementRepositoryInterface` (`paginate` berfilter produk/tipe/rentang tanggal) + `EloquentStockMovementRepository`; `StockAdjustmentRepositoryInterface` (`create`) + `EloquentStockAdjustmentRepository`; binding di `AppServiceProvider` (TASK-049).
- `StockMovementService` (pagination riwayat) + halaman `Riwayat Pergerakan Stok` (`GET /inventory/movements`, route `inventory.movements`) — kolom waktu, produk, tipe (badge), perubahan (±qty), stok sebelum → sesudah, user, catatan (TASK-050).
- `StockService::adjust()`: tetapkan stok ke nilai baru (row lock) dan catat stock movement `ADJUSTMENT` dengan selisih bertanda; menolak stok negatif/`new_stock == stok saat ini` via `ValidationException`.
- `StockAdjustmentService::adjust()`: dalam satu database transaction — update stok via `StockService`, simpan record `stock_adjustments` (before/after/difference/reason/status `completed`), menolak alasan kosong.
- Penyesuaian Stok: `StockAdjustmentPolicy` (Admin & Gudang), `StoreStockAdjustmentRequest` (produk wajib, `new_stock` ≥ 0, alasan ≥ 3 karakter), `InventoryController` routes `inventory.adjustments.create/store`, form dengan pilihan produk → tampil stok saat ini & selisih otomatis (JS inline, `aria-live`), konfirmasi sebelum simpan, prefill produk dari halaman Stok (TASK-051/052).
- Nav "Stok" di layout; CSS: `textarea` & `.stock-figure` mengikuti gaya form, `:focus-visible` diperluas ke `textarea`.
- Factories `StockMovementFactory` & `StockAdjustmentFactory`; `StockMovement`/`StockAdjustment` kini `HasFactory`.
- Tests (TASK-053): `StockAdjustmentServiceTest` (naik/turun stok + recording, tolak negatif/tidak berubah/alasan kosong, atomic rollback saat record gagal), `StockManagementTest` (halaman stok + badge, filter low-stock/keyword, riwayat & filter tipe, submit penyesuaian + update stok, validasi), `StockAuthorizationTest` (policy create Admin/Gudang, 403 Cashier/Manager di semua halaman stok), binding container repository/service.

### Changed
- `app/Models/StockMovement.php` & `app/Models/StockAdjustment.php`: tambah `HasFactory` + docblock `@use HasFactory<...>`.

## 2026-09-21

### Added
- Phase 6 — Sales: repository `SaleRepositoryInterface`/`EloquentSaleRepository` (paginate berfilter keyword/status/payment_method/rentang tanggal, `findById` + user & items.product, `nextSaleNumber` `SO-YYYYMMDD-NNNN`, create/update) dan `SaleItemRepositoryInterface`/`EloquentSaleItemRepository` (`create`) dengan binding di `AppServiceProvider` (TASK-054/055).
- `SaleService` (TASK-056): `paginate`, `findById`, dan `complete()` — dalam satu database transaction: lock produk (`findByIdForUpdate`), tolak produk nonaktif, kalkulasi subtotal dari `selling_price` server-side, selesaikan transaksi `completed`, simpan items, kurangi stok per baris (`SO-YYYYMMDD` → `SALE_OUT`, notes "Penjualan {sale_number}", reference morfable `App\Models\Sale`); non-cash memaksa `paid_amount = grand_total`, cash menolak pembayaran kurang total via `ValidationException`.
- `ProductService::searchActive()` (TASK-057): carikan produk aktif dengan stok > 0 berdasarkan nama/SKU/barcode (limit 20) untuk endpoint JSON kasir.
- POS (TASK-058/059/060/061): `GET /sales/pos` dua panel (kiri: pencarian + hasil via fetch `GET /sales/products`; kanan: keranjang dengan tambah/kurang jumlah dan hapus baris, qty dibatasi stok), kalkulasi subtotal/PPN-sementara/total & kembalian live (belum ada pajak menjadi 0), form metode pembayaran (cash/transfer/qris/card) + input nominal dengan tampilan kembalian, `aria-live` untuk total/kembalian/status.
- Stock validation & selesai transaksi (TASK-062/063/064/065): `StockService::decrease()` (row lock, tolak stok negatif via `ValidationException`, update stok + movement `SALE_OUT`); `POST /sales` menolak item kosong, metode tidak valid, pembayaran kurang total, dan stok tidak mencukupi.
- `StoreSaleRequest` (validasi sale_date, payment_method `in:cash,transfer,qris,card`, paid_amount `required_if cash` ≥ 0, items array min 1 dengan product_id exists & quantity ≥ 0.001) pesan Bahasa Indonesia; `SalePolicy` (viewAny/view/create = Admin & Kasir).
- `SaleController` + routes `sales.index/create/products/store/show`; nav "Penjualan" (gate `@can('viewAny', App\Models\Sale::class)`).
- Views: daftar riwayat penjualan (filter keyword/status/metode/tanggal, badge status, aksi Struk, pagination), halaman struk `sales/show` dengan tombol "Cetak Struk" dan aturan `@media print`; CSS POS (`.pos-layout`, `.product-list`, `.cart-summary`, `.receipt*`) + responsive 720px.
- Factories `SaleFactory` & `SaleItemFactory`; `Sale`/`SaleItem` kini `HasFactory`.
- Tests (TASK-067): `SaleServiceTest` (kalkulasi subtotal/grand/paid/change, stok berkurang + movement SALE_OUT, non-cash paid = total, tolak bayar kurang/insufficient stock/produk nonaktif, harga pakai `selling_price` server-side, nomor unik, atomic rollback saat pengurangan stok gagal, paginate/findById), `SaleManagementTest` (flow HTTP, endpoint pencarian produk, validasi, struk, riwayat), `SaleAuthorizationTest` (policy Admin+Kasir, 403 Gudang/Manager).

## 2026-09-21

### Fixed
- Input tanggal (`type="date"`) di halaman Penerimaan, Stok/Movements, dan Sales kini mengikuti design system: tinggi 44px, hairline `--hairline-01`, radius `--radius-md`, padding 16px, font 16px, `color-scheme: light` agar indikator picker kalender tampil ink-on-white sesuai token (sebelumnya memakai default browser yang lebih kecil dari input lain).

## 2026-09-21

### Added
- Phase 7 — Dashboard (TASK-069–073): `DashboardService` (summary + `salesChart(7)`) mengagregasi data via `SaleRepository` & `ProductRepository`, dikonsumsi `DashboardController`, route `dashboard` tetap, view `dashboard/index.blade.php`.
- Summary cards (TASK-069/070/072): Penjualan Hari Ini (total Rp), Transaksi Hari Ini (count selesai), Produk Aktif (jumlah aktif), Stok Menipis (stok ≤ minimum) — kartu putih hairline radius-lg.
- Sales chart (TASK-073, P1): grafik batang 7 hari terakhir berbasis CSS (tanpa dependency chart library), bar Action Blue, label hari/tanggal + nilai per kolom, total kumulatif; aksesibel (kolom memiliki teks nilai yang terlihat).
- Low-stock products (TASK-071): daftar maks. 5 produk aktif stok ≤ minimum (urutan stok asc) dengan badge stok/min, link "Lihat Semua" menuju halaman stok (hanya untuk role berizin viewAny Product).
- Repository additions: `SaleRepositoryInterface::summaryForDate()`/`chartBetween()` (DB-agnostic, `DATE()` + `whereDate` agar kompatibel MySQL & SQLite testing) dan `ProductRepositoryInterface::countActive()`/`lowStock()`/`lowStockCount()`.
- CSS: `.summary-grid`/`.summary-card`, `.dashboard-grid`/`.dashboard-panel`, `.chart*` (bar + track hairline), `.low-stock-list`/`.low-stock-item`; responsive stack `dashboard-grid` di ≤ 720px.
- Tests: `DashboardServiceTest` (summary hari ini hanya status completed, zero-fill tanpa data, low-stock mengecualikan produk nonaktif, chart 7 hari terisi nol + total benar), `DashboardTest` (render summary/chart/low-stock, empty-state, akses semua role, wajib login), binding `DashboardService` di `RepositoryBindingsTest`.
