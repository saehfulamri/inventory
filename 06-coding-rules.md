# Coding Rules & AI Development Rules

## 1. General Principles

1. KISS — pilih solusi sederhana yang memenuhi kebutuhan.
2. DRY — hindari duplikasi logic.
3. YAGNI — jangan membuat fitur sebelum dibutuhkan.
4. Single Responsibility — satu class/method memiliki tanggung jawab jelas.
5. Jangan overengineering.
6. Pertahankan konsistensi dengan kode existing.

## 2. Laravel Rules

- Ikuti convention Laravel kecuali ada alasan kuat.
- Gunakan route/controller/request/model/service secara konsisten.
- Gunakan Form Request untuk validation kompleks.
- Gunakan authorization pada server side.
- Gunakan Eloquent secara wajar.
- Gunakan database transaction untuk workflow multi-step yang harus atomic.
- Gunakan config/env untuk konfigurasi.

## 3. Service/Repository Rules

### Controller
Controller tipis.

Tidak boleh:
```php
// dozens of queries + business logic
```

Lebih baik:
```php
$this->saleService->complete($data);
```

### Service
Service berisi business process.

### Repository
Repository berisi data access/query.

Jangan membuat repository hanya sebagai wrapper method Eloquent sederhana jika abstraction tersebut tidak memberikan nilai. Namun untuk domain repository yang sudah menjadi standar proyek, ikuti pola yang konsisten.

## 4. Naming

Class:
```text
ProductService
SaleService
EloquentProductRepository
```

Method:
```text
findBySku()
create()
update()
completeSale()
```

Variable:
```php
$product
$saleItems
$grandTotal
```

Hindari:
```php
$x
$data1
$temp
```

kecuali scope sangat kecil dan maknanya jelas.

## 5. Blade Rules

- Gunakan layout/component yang reusable.
- Hindari duplikasi markup.
- Jangan menaruh business calculation kompleks di Blade.
- Escape output secara default.
- Gunakan partial/component untuk pola UI berulang.

## 6. Validation

Validation dilakukan sebelum business process.

Contoh:
- required
- numeric
- min
- unique
- exists

Business validation tetap harus dilakukan di Service ketika bergantung pada kondisi database saat transaksi berjalan.

Contoh stok:
Validation request saja tidak cukup. Service harus memeriksa stok aktual sebelum finalisasi.

## 7. Security

- Jangan commit `.env`.
- Jangan hard-code password/database credential.
- Jangan menyimpan password plaintext.
- Jangan percaya data dari browser.
- Gunakan authorization server-side.
- Hindari raw SQL jika query builder/Eloquent mencukupi.
- Jika raw SQL diperlukan, gunakan parameter binding.
- Hindari mass assignment yang tidak terkontrol.
- Jangan menampilkan stack trace di production.
- Jangan log password, token, atau data rahasia.

## 8. Database Transactions

Gunakan transaction untuk proses seperti:

```text
Complete Sale
    ├── create sale
    ├── create sale items
    ├── decrease stock
    └── create stock movements
```

Jika salah satu gagal, rollback seluruh proses.

## 9. Stock Rules

Semua perubahan stok harus memiliki sumber yang jelas.

Dilarang:
```php
$product->stock -= $qty;
$product->save();
```

di sembarang controller.

Gunakan service khusus/proses domain.

## 10. Testing

Setiap fitur penting harus memiliki test yang relevan.

Minimal untuk penjualan:
- dapat membuat transaksi valid,
- menolak stok tidak cukup,
- stok berkurang setelah transaksi,
- stock movement tercatat,
- transaksi rollback jika proses gagal,
- unauthorized user ditolak.

## 11. Git

Commit convention:

```text
feat: add product CRUD
fix: prevent negative stock on sale
refactor: extract sale logic into service
test: add sale stock validation tests
docs: update database design
chore: update dependencies
```

Commit harus fokus pada satu logical change.

## 12. AI/Vibe Coding Rules

Sebelum coding:

1. Baca `01-project-brief.md`.
2. Baca requirement yang relevan.
3. Baca architecture.
4. Baca database design jika menyentuh data.
5. Baca coding rules.
6. Periksa kode existing.
7. Identifikasi file yang benar-benar perlu diubah.

Saat coding:

1. Kerjakan satu task pada satu waktu.
2. Jangan mengubah file unrelated.
3. Jangan membuat dependency baru tanpa alasan.
4. Jangan mengganti architecture.
5. Jangan menghapus working feature tanpa persetujuan.
6. Jangan mengarang requirement.
7. Jika requirement ambigu dan berdampak pada desain/data, minta klarifikasi.

Setelah coding:

1. Jalankan formatter/linter bila tersedia.
2. Jalankan test yang relevan.
3. Periksa migration.
4. Periksa authorization.
5. Periksa validation.
6. Periksa dampak terhadap stok/transaksi.
7. Laporkan file yang berubah.
8. Laporkan test yang dijalankan dan hasilnya.

## 13. AI Output Format

Untuk task yang cukup besar, AI sebaiknya memberikan:

### Plan
File dan langkah yang akan diubah.

### Implementation
Perubahan kode.

### Verification
Test/command yang dijalankan.

### Result
Ringkasan hasil dan hal yang masih perlu diperhatikan.

## 14. Definition of Done

Task selesai apabila:
- Requirement terpenuhi.
- Architecture tetap konsisten.
- Validation tersedia.
- Authorization tersedia jika diperlukan.
- Test relevan lulus.
- Tidak ada error yang diketahui.
- Tidak ada perubahan unrelated.
- Dokumentasi diperbarui bila diperlukan.
