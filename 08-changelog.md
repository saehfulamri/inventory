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

## 2026-09-25

### Added
- Fase 0 — TASK-103: fondasi Inertia + Vue 3 (SPA): dependensi `inertiajs/inertia-laravel ^3.3` dan `tightenco/ziggy ^2.6`; npm `@inertiajs/vue3 ^3.7.1`, `vue ^3.5.43`, `@vitejs/plugin-vue ^6.0.9`, `ziggy-js ^2.6.4`; root template `resources/views/app.blade.php`; middleware `HandleInertiaRequests` (shared props `app`/`auth`/`can`/`flash`); plugin Vue di `vite.config.js`; CI: Setup Node 22 + `npm ci` + `npm run build`.
- Fase 1 — TASK-104/105: komponen UI reusable (`AppLayout.vue` + 10 komponen `ui/`) mengikuti token `09-design.md`; halaman login Inertia; komponen Error (403/404/500) via `Inertia::handleExceptionsUsing()`.
- Fase 2 — TASK-106: migrasi halaman Dashboard ke Inertia (`Pages/Dashboard/Index.vue`); view Blade `dashboard/index.blade.php` dihapus; test `DashboardTest` dialihkan ke assertion `assertInertia`.
- Fase 3 — TASK-107: migrasi modul Produk ke Inertia — halaman `Pages/Products/{Index,Create,Edit}.vue` + komponen form bersama `Components/Products/ProductForm.vue` (upload foto via `useForm`, fallback multipart otomatis saat ada file); util bersama `resources/js/utils/format.js` (format Rupiah); prop `min`/`step` pada `VFormField`; view Blade `products/*` dihapus; test `ProductManagementTest` & `ProductAuthorizationTest` dialihkan ke `assertInertia`.
- Fase 4 — TASK-108: migrasi modul Supplier ke Inertia — halaman `Pages/Suppliers/{Index,Create,Edit}.vue` + komponen form bersama `Components/Suppliers/SupplierForm.vue`; shared prop baru `can.viewAnySuppliers`; link "Supplier" dikembalikan ke nav `AppLayout.vue`; view Blade `suppliers/*` dihapus; test `SupplierManagementTest` & `SupplierAuthorizationTest` dialihkan ke `assertInertia`.
- Fase 5 — TASK-109: migrasi modul Inventory ke Inertia — halaman `Pages/Inventory/{Index,Movements}.vue` + `Pages/Inventory/Adjustments/Create.vue` (stok list dengan filter, riwayat pergerakan stok, form penyesuaian dengan output stok saat ini & selisih live); util baru `formatQuantity` & `formatDateTime` di `resources/js/utils/format.js`; view Blade `inventory/*` dihapus; test `StockManagementTest` & `StockAuthorizationTest` dialihkan ke `assertInertia`.

### Changed
- Keputusan arsitektur frontend: migrasi view layer dari **Blade Template** ke **Vue.js 3 + Inertia.js (SPA)**. Rencana dan konvensi didokumentasikan; implementasi berjalan bertahap per modul sesuai `07-task-backlog.md` (Phase 12, TASK-103–115). Backend (Controller → Service → Repository → Eloquent), seluruh route, dan business logic tidak berubah. Blade menyisakan satu root template (`resources/views/app.blade.php`).
- `01-project-brief.md`: teknologi frontend diperbarui ke Vue.js 3 + Inertia.js (plus Ziggy, Vite/Tailwind v4).
- `02-requirements.md`: tambah NFR-011 Frontend Architecture [P0] (SPA via Inertia; business logic tetap server-side; endpoint JSON POS tetap dipertahankan).
- `03-product-spec.md`: referensi "Blade components/layouts" diganti "Komponen Vue (SFC) reusable".
- `04-architecture.md`: presentation layer diperbarui ke Inertia/Vue — alur presentasi, tanggung jawab `Vue (Inertia Page)`, struktur `resources/js`, konvensi Inertia/Vue, dan penegasan authorization tidak boleh hanya disembunyikan di frontend.
- `06-coding-rules.md`: seksi "Blade Rules" diganti "Vue/Inertia Rules (Frontend)".
- `09-design.md`: tambah seksi "Implementasi di Frontend (Vue/Inertia)" — token desain dikonsumsi komponen Vue; UI reusable sebagai SFC.
- `05-database.md`: tambah catatan bahwa migrasi frontend tidak mengubah skema database maupun kontrak data.
- `10-deployment.md`: kebutuhan Node dan langkah build aset frontend diperbarui (bundel Vue/Inertia + Tailwind); `view:cache` hanya mencakup root template Blade.
- `11-backup-restore.md`: aset yang tidak perlu di-backup ditambah `public/build/` (regenerable via `npm run build`).
- `README.md`: seksi Arsitektur & Prasyarat diperbarui ke Vue.js 3/Inertia; langkah instalasi kini mencakup build aset frontend.
- `CONTRIBUTING.md`: prasyarat, aturan authorization, dan alur dependensi fitur diperbarui ke frontend Vue/Inertia.

### Notes
- Fase ini baru mendokumentasikan rencana (planning). Implementasi setiap modul akan dicatat pada changelog di rilis berikut.

### Added
- Middleware `SecurityHeaders` (global) yang mengirim header keamanan pada semua respons web: `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` (nonaktif geolocation/microphone/camera), dan **Content-Security-Policy baseline** (`default-src 'self'`, `object-src 'none'`, `frame-ancestors 'self'`, `form-action 'self'`, `upgrade-insecure-requests`; `script-src`/`style-src` masih `'unsafe-inline'` karena inline handler & `<script>` pada POS/form yang ada — target refactor bertahap ke nonce/hash).
- `tests/Feature/Security/SecurityHeadersTest.php`: header kehadiran di halaman guest & authenticated, serta kebenaran baseline CSP (3 test).

### Changed
- `bootstrap/app.php`: aktifkan middleware `TrustHosts` (proteksi Host header poisoning; otomatis nonaktif di env `local`/testing) dan daftarkan `SecurityHeaders` sebagai global middleware.
- `ReportService::exportSales()`: pagination streaming kini menggunakan parameter halaman eksplisit (`paginate(..., $perPage, $pageNumber)`) menggantikan mutasi global `request()->merge(['page' => ...])` — responsivitas terhadap request bersih, tanpa efek samping global.
- `SaleRepositoryInterface` & `EloquentSaleRepository`: `paginate()` menerima argumen opsional `?int $page = null` (diteruskan ke `LengthAwarePaginator`; saat null tetap resolve dari request seperti sebelumnya).
- `.env.example`: tambah dokumentasi `SESSION_SECURE_COOKIE=true` (commented) — wajib aktif di produksi HTTPS agar cookie session tidak dikirim lewat HTTP.

### Security
- Hasil review keamanan menyeluruh (lih. catatan): SQL injection (semua kueri terparameterisasi), XSS (semua output auto-escaped), CSRF (10/10 form memakai `@csrf`), session fixation (regenerate + invalidate), RBAC (semua endpoint pakai `authorize()`), mass assignment (`#[Fillable]` di 11 model), upload file (MIME asli + ukuran), CSV formula injection, dependensi (`composer audit` 0 advisory) — seluruhnya lulus. Gap yang ditutup di rilis ini: security headers + CSP baseline, trusted hosts, dokumentasi secure cookie.

## 2026-09-24

### Added
- Modul Kelola Supplier (Phase 11, FR-SUP-001/004/005): halaman daftar supplier (`suppliers/index`) dengan filter kata kunci (nama/kode) & status aktif/nonaktif + pagination, form tambah (`suppliers/create`) & edit (`suppliers/edit`) lewat partial `_form`, dan aksi "Nonaktifkan" (soft-disable — histori penerimaan barang tetap utuh, bisa diaktifkan lagi lewat form edit). Tambah route `suppliers.*` (`index/create/store/edit/update/deactivate`), `SupplierController`, link "Supplier" di topbar (di-gate `@can('viewAny', ...)`).
- `StoreSupplierRequest` / `UpdateSupplierRequest`: validasi `code` (nullable, unik kecuali diri sendiri, max 50), `name` (required, max 150), `phone` (nullable, max 30), `email` (nullable, format email), `address` (nullable, max 1000), `is_active` (boolean) — dengan pesan Bahasa Indonesia.
- `SupplierPolicy` (Admin & Gudang untuk `viewAny`/`create`/`update`/`delete`) — konsisten dengan `ProductPolicy`.
- Tests: `SupplierManagementTest` (lihat daftar, empty state, buka form, create, wajib `name`, tolak `code` duplikat & email tidak valid, update, nonaktifkan tanpa menghapus, filter kata kunci & status) dan `SupplierAuthorizationTest` (policy Admin/Gudang ✓, Kasir/Manager ditolak — termasuk 403 di tiap endpoint).

### Changed
- `routes/web.php`: grup route `suppliers.*` di dalam middleware `auth`.
- `resources/views/layouts/app.blade.php`: tambah link "Supplier" di topnav (muncul hanya untuk Admin & Gudang via policy).
- `02-requirements.md`: tambah FR-SUP-004 (Admin/Gudang dapat mengubah data supplier) [P0], FR-SUP-005 (daftar supplier dapat dicari & difilter status) [P0], FR-SUP-006 (kode supplier opsional & unik) [P1].
- `07-task-backlog.md`: tambah Phase 11 — Supplier Management (TASK-097–102), ditandai selesai.
- `README.md`: seksi Fitur Utama & Status diperbarui (modul kelola supplier).

### Added
- Persiapan open source: lisensi MIT (`LICENSE`), workflow GitHub Actions (`.github/workflows/tests.yml` — Pint + PHPUnit di PHP 8.3 & 8.4, memakai SQLite in-memory tanpa service MySQL), badge lisensi/PHP/Laravel/Tests di README, daftar fitur utama, `CONTRIBUTING.md` (panduan kontribusi: setup, konvensi, alur git, checklist PR), serta template issue (`bug_report`, `feature_request`) dan template Pull Request di `.github/`.

### Changed
- `composer.json`: `name` dari `laravel/laravel` → `saehfulamri/inventory`.
- `README.md`: lisensi dari privat/internal → MIT; tambah bagian "Fitur Utama", "Data Demo" (seeder + akun 4 role), "Kontribusi" (tautan ke `CONTRIBUTING.md`), "Lisensi", dan "Status" yang diperbarui.
- `08-changelog.md`: koreksi klaim laporan — laporan penjualan hanya filter tanggal (bukan status/metode bayar), laporan stok tanpa indikator "expired" (fitur tidak ada), laporan pembelian tanpa filter supplier (belum di-wire), wording "sidebar" → "topbar", dan hapus duplikasi seksi Architecture.
- Pint: perbaikan style 6 file; import tidak terpakai (`CategoryRepositoryInterface as CatInterfaceAlias`) di `AppServiceProvider` dihapus.
- Persyaratan PHP dinaikkan ke 8.4 (kompatibel dengan lock file): `composer.json` `"php": "^8.4"`, matrix CI PHP 8.4 & 8.5, badge README dan prasyarat di README/CONTRIBUTING disesuaikan.

### Fixed
- `resources/views/reports/purchases.blade.php`: kolom Total memakai `$purchase->total_amount` (sebelumnya `$purchase->grand_total` yang tidak ada pada model Purchase → selalu tampil Rp 0,00). Assertion nilai total ditambahkan di `ReportFeatureTest`.

### Security
- Ekspor CSV laporan penjualan: nilai sel yang diawali karakter yang dieksekusi spreadsheet (`=`, `+`, `-`, `@`, tab, CR) kini diberi awalan kutip tunggal untuk mencegah CSV formula injection (OWASP) + test baru.
- Route `POST /login` kini memakai rate limiting (`throttle:5,1`) untuk memperlambat serangan brute-force.
- Review keamanan selesai (TASK-082–085): validasi via Form Request dengan pesan Bahasa Indonesia, tidak ada output Blade tidak ter-escape (`{!! !!}`), semua form memakai `@csrf`, tidak ada log sensitif, dan mass assignment aman — seluruh model memakai `#[Fillable(...)]` dan tidak ada `$request->all()`.

### Changed
- `07-task-backlog.md`: tandai selesai TASK-079–090 dan TASK-093–095 (dievaluasi ulang berdasarkan kondisi kode aktual; tersisa TASK-091 Deployment, TASK-092 Backup/restore, dan TASK-096 Foto produk).
- `.env.example`: `APP_NAME` dari `Laravel` → `Sistem Inventori & Penjualan`.

## 2026-09-24

### Added
- Fitur foto produk (TASK-096): kolom nullable `image_path` pada `products` (migrasi baru), upload JPG/PNG/WEBP ≤ 2 MB via Form Request (`nullable|image|mimes:jpeg,png,webp|max:2048`), file disimpan ke disk `public` (`storage/app/public/products/…`, nama acak + ekstensi asli), akses publik lewat `php artisan storage:link`. Thumbnail ditampilkan lewat CSS `object-fit` (tanpa dependency image-processing). Tampil di: daftar produk (kolom Foto), form create/edit (preview + upload), hasil pencarian & keranjang POS (via `image_url` di endpoint `GET /sales/products`). Foto lama otomatis dihapus saat diganti (`ProductImageService::replace`). Test: create dengan foto, tolak file non-gambar, ganti foto.
- `10-deployment.md` (TASK-091): panduan deploy produksi — persyaratan server, contoh `.env` produksi, `migrate --force`, `storage:link`, build aset, cache produksi, contoh Nginx & Caddy, checklist keamanan go-live, proses update/rollback rilis.
- `11-backup-restore.md` (TASK-092): prosedur backup database (`mysqldump --single-transaction` + gzip), backup storage & `.env`, skrip `backup.sh` + cron harian + rotasi, prosedur restore penuh/parsial dengan urutan kode→env→storage→database, verifikasi restore.

### Changed
- `07-task-backlog.md`: tandai selesai TASK-091, TASK-092, dan TASK-096 — Phase 10 (Quality & Release) kini tuntas; semua fase roadmap selesai.
- `README.md`: indeks dokumentasi + `10-deployment.md` & `11-backup-restore.md`; instalasi development + langkah `php artisan storage:link` (diperlukan untuk foto produk); seksi Status diperbarui.
- `05-database.md`: daftar kolom `products` + `image_path` (nullable, disimpan di disk `public`, bukan di DB).
- `app/Models/Product.php`: tambah accessor `image_url` (URL publik foto produk dari `Storage::url`, atau null bila tidak ada foto).
- `app/Services/ProductImageService.php` (baru): abstraksi penyimpanan/penghapusan foto — `store()`, `replace()`, `delete()` — dipakai `ProductController` (store/update).

### Database
- Baru: `2026_09_24_000001_add_image_path_to_products_table` — `products.image_path` (string, nullable, setelah `minimum_stock`).

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
