# Project Brief — Sistem Inventori & Penjualan (Mini Supermarket)

## 1. Ringkasan

Sistem Inventori & Penjualan (Mini Supermarket) adalah aplikasi web untuk membantu mini supermarket mengelola master produk, stok, pembelian/penerimaan barang, penjualan, pengguna, supplier, serta laporan operasional.

Aplikasi dibangun sebagai monolith Laravel dengan Blade Template dan MySQL. Business logic dipisahkan dari controller menggunakan Service/Repository Pattern agar kode mudah diuji, dirawat, dan dikembangkan.

## 2. Tujuan

- Mengelola data produk secara terpusat.
- Memantau stok secara akurat.
- Mencatat penerimaan/pembelian barang.
- Mencatat transaksi penjualan.
- Mengurangi kesalahan pencatatan stok manual.
- Menyediakan laporan operasional yang relevan.
- Menyediakan fondasi yang mudah dikembangkan dengan bantuan AI coding agent.

## 3. Target Pengguna

### Admin
Mengelola pengguna, master data, konfigurasi, dan seluruh operasional.

### Kasir
Melakukan transaksi penjualan dan melihat informasi produk/stok yang diperlukan untuk transaksi.

### Petugas Gudang
Mengelola penerimaan barang, penyesuaian stok, dan monitoring persediaan.

### Owner/Manager
Melihat dashboard dan laporan bisnis tanpa melakukan perubahan data operasional tertentu.

## 4. Ruang Lingkup MVP

### Master Data
- Produk
- Kategori
- Satuan
- Supplier
- Pengguna dan role

### Inventory
- Stok per produk
- Penerimaan barang
- Penyesuaian stok
- Kartu/riwayat pergerakan stok
- Stok minimum

### Penjualan
- Transaksi penjualan
- Detail transaksi
- Perhitungan subtotal, diskon, pajak bila diaktifkan, dan grand total
- Pembayaran
- Perubahan stok otomatis setelah transaksi berhasil

### Laporan
- Penjualan harian/periode
- Produk terlaris
- Stok saat ini
- Stok minimum
- Riwayat pergerakan stok
- Ringkasan pembelian/penerimaan

## 5. Di Luar MVP

Fitur berikut tidak menjadi prioritas awal:

- Multi-cabang kompleks
- Integrasi marketplace
- Integrasi payment gateway
- Loyalty/customer membership kompleks
- Akuntansi penuh
- Mobile app native
- Integrasi barcode scanner khusus

Fitur tersebut dapat dipertimbangkan setelah MVP stabil.

## 6. Teknologi

- PHP
- Laravel
- Blade Template
- MySQL
- Laravel Eloquent
- Laravel Validation/Form Request
- Laravel Authentication sesuai kebutuhan versi Laravel
- CSS/JavaScript dengan pendekatan sederhana dan maintainable
- Composer
- Git

## 7. Prinsip Produk

1. Utamakan akurasi stok.
2. Transaksi harus dapat ditelusuri.
3. UI harus sederhana untuk kasir.
4. Business logic tidak ditempatkan di controller.
5. Jangan menambahkan kompleksitas tanpa kebutuhan.
6. Semua perubahan stok harus memiliki alasan/sumber transaksi.
7. Data transaksi yang sudah final tidak boleh diubah secara sembarangan.

## 8. Definisi Sukses MVP

MVP dianggap siap ketika:

- Admin dapat mengelola master data.
- Produk dapat memiliki stok.
- Petugas dapat menerima barang.
- Stok bertambah berdasarkan penerimaan.
- Kasir dapat membuat transaksi penjualan.
- Stok berkurang berdasarkan penjualan.
- Sistem menolak penjualan dengan stok tidak mencukupi.
- Riwayat pergerakan stok dapat ditelusuri.
- Laporan dasar dapat digunakan.
- Hak akses role berjalan.
- Automated tests untuk business logic kritis tersedia.
- Tidak terdapat credential rahasia di repository.
