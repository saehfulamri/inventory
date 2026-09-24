# Product Specification — Sistem Inventori & Penjualan

## 1. Prinsip UX

- Sederhana.
- Konsisten.
- Responsif.
- Feedback jelas setelah aksi.
- Error validation mudah dipahami.
- Aksi destruktif membutuhkan konfirmasi.
- Kasir harus dapat menyelesaikan transaksi dengan sedikit interaksi.
- Frontend memenuhi standar aksesibilitas WCAG 2.2 level AA, karena sistem digunakan oleh berbagai kalangan pengguna.

### Aksesibilitas (WCAG AA)

Tampilan frontend harus memenuhi kriteria WCAG 2.2 level AA, minimal:

- Kontras teks terhadap latar belakang minimal 4.5:1 untuk teks normal (WCAG 1.4.3); kontras komponen antarmuka/grafik non-teks minimal 3:1 (1.4.11).
- Seluruh fungsi dapat dioperasikan dengan keyboard (2.1.1), termasuk navigasi, form, dan dialog; indikator fokus terlihat jelas saat navigasi keyboard (2.4.7).
- Setiap input memiliki label programatik (`<label>` atau `aria-label`) terkait secara semantik (1.3.1, 4.1.2).
- Pesan error diidentifikasi dan dihubungkan ke input terkait (3.3.1); saran perbaikan diberikan apabila memungkinkan (3.3.3); instruksi/petunjuk tidak hanya mengandalkan warna.
- Konten dapat di-resize hingga 200% tanpa kehilangan fungsi (1.4.4).
- Bahasa halaman dinyatakan (`lang`) dan struktur semantic (landmark/heading) diperhatikan (3.1.1, 2.4.6).
- Tombol dan konten penting memiliki nama tekstual deskriptif, bukan hanya ikon/warna (2.5.3, 4.1.2).

## 2. Struktur Navigasi

### Admin
- Dashboard
- Produk
- Kategori
- Satuan
- Supplier
- Penerimaan
- Penjualan
- Stok
- Laporan
- Pengguna
- Pengaturan

### Kasir
- Dashboard ringkas
- Penjualan
- Riwayat penjualan

### Gudang
- Dashboard
- Produk
- Supplier
- Penerimaan
- Stok
- Stock Adjustment
- Riwayat Stock Movement

### Manager/Owner
- Dashboard
- Laporan Penjualan
- Laporan Stok
- Laporan Pergerakan Stok

## 3. User Flow — Penjualan

1. Kasir membuka halaman Penjualan.
2. Sistem membuat draft/cart transaksi.
3. Kasir mencari produk.
4. Produk dipilih.
5. Qty dimasukkan.
6. Sistem menampilkan subtotal.
7. Kasir mengulangi langkah 3-6 jika ada item lain.
8. Sistem menghitung grand total.
9. Kasir memilih metode pembayaran.
10. Jika tunai, kasir memasukkan nominal pembayaran.
11. Sistem menghitung kembalian.
12. Kasir klik "Bayar".
13. Sistem melakukan validasi ulang stok.
14. Sistem menyimpan transaksi dan detail.
15. Sistem mengurangi stok.
16. Sistem mencatat stock movement.
17. Sistem menampilkan hasil transaksi.
18. Jika diperlukan, kasir dapat mencetak receipt.

## 4. User Flow — Penerimaan Barang

1. Gudang membuka Penerimaan.
2. Klik "Tambah Penerimaan".
3. Pilih supplier.
4. Masukkan tanggal.
5. Tambahkan produk.
6. Masukkan qty dan harga beli.
7. Sistem menghitung subtotal.
8. User memeriksa data.
9. Klik "Simpan/Finalisasi".
10. Sistem menjalankan database transaction.
11. Header dan detail penerimaan disimpan.
12. Stok produk bertambah.
13. Stock movement IN dibuat.
14. Transaksi dinyatakan berhasil.

## 5. User Flow — Stock Adjustment

1. User Gudang membuka Adjustment.
2. Memilih produk.
3. Sistem menampilkan stok saat ini.
4. User menentukan stok baru atau selisih.
5. User wajib memasukkan alasan.
6. Sistem menghitung perubahan.
7. User mengonfirmasi.
8. Sistem menyimpan adjustment.
9. Stok diperbarui.
10. Stock movement ADJUSTMENT dibuat.

## 6. Halaman Produk

### Table
Kolom minimal:
- SKU
- Barcode
- Nama
- Kategori
- Harga Beli
- Harga Jual
- Stok
- Stok Minimum
- Status
- Action

### Action
- Detail
- Edit
- Nonaktifkan

### Filter
- Keyword
- Kategori
- Status
- Stok minimum

## 7. Halaman Penjualan

Layout disarankan dua area pada desktop:
- Area pencarian/product selection
- Area cart dan pembayaran

Pada mobile, area ditumpuk secara vertikal.

Informasi penting:
- Nama produk
- Harga
- Qty
- Subtotal
- Total
- Metode pembayaran
- Pembayaran
- Kembalian

## 8. Status Transaksi

### Sales
- DRAFT
- COMPLETED
- VOID/CANCELLED bila mekanisme pembatalan disediakan

### Purchase/Receiving
- DRAFT
- COMPLETED
- CANCELLED bila mekanisme pembatalan disediakan

Jangan menghapus transaksi final secara fisik hanya untuk "membatalkan". Gunakan mekanisme pembatalan/void yang menyimpan histori.

## 9. Validation UX

Contoh:
- "SKU wajib diisi."
- "SKU sudah digunakan."
- "Stok tidak mencukupi. Stok tersedia: 3."
- "Jumlah pembayaran kurang dari total transaksi."
- "Alasan adjustment wajib diisi."

## 10. Feedback

Success:
- Gunakan flash message yang konsisten.

Error:
- Tampilkan pesan yang actionable.
- Jangan menampilkan stack trace kepada user production.

## 11. Empty State

Setiap daftar harus memiliki empty state.

Contoh:

> Belum ada produk.
> Tambahkan produk pertama untuk mulai mengelola inventori.

## 12. Confirmation

Aksi yang berpotensi merusak data:
- Nonaktifkan produk
- Finalisasi transaksi
- Void transaksi
- Adjustment stok

harus memiliki konfirmasi yang jelas.

## 13. Responsive

Prioritas:
1. Desktop kasir/gudang.
2. Tablet.
3. Mobile.

Blade components/layouts digunakan agar UI tidak diduplikasi.

## 14. Acceptance Criteria Umum

Sebuah fitur dianggap selesai jika:
- Happy path berjalan.
- Validation berjalan.
- Authorization berjalan.
- Error path ditangani.
- Tidak merusak fitur terkait.
- Automated tests yang relevan lulus.
- UI mengikuti pola existing.
- Tampilan baru memenuhi kriteria aksesibilitas WCAG 2.2 level AA (lihat Prinsip UX).
- Dokumentasi/changelog diperbarui jika ada perubahan penting.
