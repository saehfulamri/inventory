# Panduan Kontribusi — Sistem Inventori & Penjualan

Terima kasih sudah ingin berkontribusi! 🎉 Proyek ini adalah aplikasi monolith Laravel dengan arsitektur Service/Repository. Panduan ini merangkum cara berkontribusi dengan rapi dan sesuai konvensi proyek.

## Jenis Kontribusi

- 🐛 **Laporan bug** — buka issue dengan langkah reproduksi, output yang diharapkan vs aktual, dan environment (PHP/MySQL versi).
- 💡 **Permintaan fitur** — buka issue *feature request* dan jelaskan nilai bisnisnya.
- 🔧 **Pull request** — perbaikan bug, fitur kecil, perbaikan dokumentasi, atau peningkatan test.
- 📚 **Dokumentasi** — perbaikan typo, penjelasan yang kurang jelas, atau contoh penggunaan.

## Persiapan Lingkungan Development

Prasyarat: PHP ≥ 8.4, Composer, dan (opsional) Node.js & npm untuk frontend.

```sh
# 1. Install dependensi
composer install

# 2. Siapkan environment
cp .env.example .env
php artisan key:generate

# 3a. (Paling cepat) Pakai SQLite untuk development lokal
touch database/database.sqlite
# pastikan .env memakai DB_CONNECTION=sqlite

# 3b. ATAU gunakan MySQL (sesuai kebutuhan produksi)
mysql -u root -p -e "CREATE DATABASE inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# lalu set DB_* di .env

# 4. Jalankan migrasi + seeder data demo
php artisan migrate --seed
# (opsional) build aset frontend
npm install && npm run build

# 5. Jalankan server
php artisan serve
```

Akun demo (hanya tersedia di env `local`/`testing`, password: `password`):

| Role | Email |
| --- | --- |
| Admin | `admin@example.com` |
| Kasir | `kasir@example.com` |
| Petugas Gudang | `gudang@example.com` |
| Manager | `manager@example.com` |

> ⚠️ Jangan pernah meng-commit `.env`. File ini sudah ter-*ignore* oleh `.gitignore`.

## Arsitektur & Struktur

Sebelum mengubah kode, pahami lapisan arsitektur (detail lengkap: [`04-architecture.md`](04-architecture.md)):

```text
Controller (tipis)
    ↓  panggil Service
Service (business logic, transaction boundary)
    ↓  panggil Repository
Repository (data access) → Eloquent Model → MySQL
```

Alur validasi: `HTTP Request → Form Request → Controller → Service`.

Aturan inti yang **tidak bisa ditawar**:

1. **Controller harus tipis** — tidak boleh berisi business logic kompleks.
2. **Semua perubahan stok melalui Service terpusat** (`StockService::increase/decrease/adjust`) — jangan pernah `$product->stock -= $qty` langsung di controller.
3. **Setiap perubahan stok mencatat stock movement** dengan sumber transaksi (`PURCHASE_IN`, `SALE_OUT`, `ADJUSTMENT`, dst).
4. **Operasi multi-step menggunakan database transaction** (`DB::transaction`) — kalau satu langkah gagal, seluruh proses rollback.
5. **Authorization selalu di server-side** via Policy/Gate — menyembunyikan tombol di Blade saja tidak cukup.
6. **Stok/minimum & uang**: gunakan tipe konsisten (`decimal`) sesuai migrasi.

Struktur direktori utama:

```text
app/
├── Http/Controllers/     # route + controller tipis
├── Http/Requests/        # Form Request (validation + pesan)
├── Models/               # entity + relasi + casts
├── Policies/             # authorization per role
├── Repositories/
│   ├── Contracts/        # interface
│   └── Eloquent/         # implementasi query
├── Services/             # business process
└── Enums/                # domain enum (Role, MovementType, dll.)
```

## Konvensi Coding

Ikuti [`06-coding-rules.md`](06-coding-rules.md) secara penuh. Ringkasannya:

- **Nama**: gunakan nama deskriptif (`$product`, `$grandTotal`); hindari `$x`, `$temp`.
- **Wire repository**: tambahkan binding interface → implementasi di `AppServiceProvider` bila Anda menambah repository baru.
- **Form Request**: validasi kompleks dan pesan error Bahasa Indonesia di sini.
- **Validation bisnis** yang bergantung kondisi DB saat transaksi (contoh: stok aktual) dilakukan di **Service**, bukan hanya di request.
- **Jangan menambah dependency** tanpa alasan, dan jangan mengganti pola arsitektur tanpa diskusi.

### Menambahkan fitur baru

Ikuti alur dependensi (lihat juga `07-task-backlog.md`):

```text
Migration → Model → Repository → Service → Controller → Blade → Test
```

Cocokkan dengan task di backlog; hindari mengerjakan task yang dependensinya belum ada.

## Code Style (Pint)

Proyek menggunakan [Laravel Pint](https://laravel.com/docs/pint) sebagai formatter resmi:

```sh
# Periksa adherence
vendor/bin/pint --test

# Otomatis perbaiki
vendor/bin/pint
```

Pastikan `vendor/bin/pint --test` lulus sebelum push — CI juga menjalankan pint.

## Menjalankan Test

Test memakai SQLite in-memory (tidak butuh MySQL) dan dikonfigurasi di `phpunit.xml`.

```sh
# Seluruh test suite
composer test
# atau
php artisan test

# Test spesifik
php artisan test --filter=SaleServiceTest
```

Setiap perubahan **harus** menyertakan/update test yang relevan. Prioritas test sesuai `04-architecture.md`: Sale → Stock → Purchase/Receiving → Authorization → Product validation.

## Alur Kerja Git

### 1. Branch

Kerjakan di branch terpisah, bukan langsung di `main`. Penamaan branch:

```text
feat/tambah-filter-supplier
fix/perbaiki-kalkulasi-kembalian
docs/perbarui-panduan-installasi
test/tambah-test-finalisasi-penerimaan
```

### 2. Commit

Gunakan [Conventional Commits](https://www.conventionalcommits.org/), satu logical change per commit:

```text
feat: add supplier filter to purchase report
fix: prevent negative stock on sale
refactor: extract sale logic into service
test: add sale stock validation tests
docs: update database design
chore: update dependencies
```

### 3. Pull Request

- Deskripsikan **apa, mengapa, dan bagaimana** perubahannya.
- Hubungkan ke issue/backlog task jika ada (`TASK-063`, `#12`).
- Sebutkan test yang dijalankan dan hasilnya.
- Pastikan checklist di bawah terpenuhi.

### Checklist PR

- [ ] `composer test` lulus (atau minimal test relevan)
- [ ] `vendor/bin/pint --test` lulus
- [ ] Validation (Form Request) sudah ditambahkan bila ada input baru
- [ ] Authorization (Policy/Gate) sudah ditambahkan/konsisten bila halaman/menu baru
- [ ] Perubahan stok/transaksi memakai Service + transaction + stock movement
- [ ] Tidak ada perubahan file yang tidak relevan
- [ ] Dokumentasi (README / `08-changelog.md`) diperbarui bila diperlukan

## Kontribusi dengan AI Coding Agent

Proyek ini dirancang agar mudah dikerjakan bersama AI coding agent (mis. Codex, Claude Code, Cursor). Jika Anda memakai agent:

- Baca dulu: `01-project-brief.md`, requirement terkait, `04-architecture.md`, `05-database.md` (bila menyentuh data), dan `06-coding-rules.md`.
- Kerjakan satu task dalam satu waktu; jangan ubah file yang tidak terkait.
- `AGENTS.md` & `CLAUDE.md` di repo berisi instruksi bootstrap bagi agent.
- Ikuti format output: Plan → Implementation → Verification → Result.

## Definition of Done

A task dianggap selesai bila (dari `06-coding-rules.md`):

- Requirement terpenuhi.
- Arsitektur tetap konsisten.
- Validation tersedia.
- Authorization tersedia jika diperlukan.
- Test relevan lulus.
- Tidak ada error yang diketahui.
- Tidak ada perubahan unrelated.
- Dokumentasi diperbarui bila diperlukan.

## Lisensi

Dengan berkontribusi, Anda menyetujui bahwa kontribusi Anda dirilis di bawah lisensi [MIT](LICENSE).

---
Terima kasih sudah membantu membuat proyek ini lebih baik! 🙌