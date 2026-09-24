# Requirements — Sistem Inventori & Penjualan

## 1. Konvensi

Prioritas:
- P0 = wajib untuk MVP
- P1 = penting setelah MVP
- P2 = enhancement

Jenis requirement:
- Functional Requirement (FR)
- Non-Functional Requirement (NFR)

## 2. Authentication & Authorization

### FR-AUTH-001 [P0]
User dapat login menggunakan credential yang valid.

### FR-AUTH-002 [P0]
User dapat logout.

### FR-AUTH-003 [P0]
Sistem memiliki role minimal:
- Admin
- Kasir
- Gudang
- Manager/Owner

### FR-AUTH-004 [P0]
Akses menu dan aksi dibatasi berdasarkan role.

### FR-AUTH-005 [P0]
Password disimpan menggunakan mekanisme hashing Laravel.

### FR-AUTH-006 [P1]
Sistem mencatat waktu login/logout atau aktivitas penting user.

## 3. Master Produk

### FR-PROD-001 [P0]
Admin/Gudang dapat menambahkan produk.

### FR-PROD-002 [P0]
Produk memiliki minimal:
- SKU/kode
- Barcode opsional
- Nama
- Kategori
- Satuan
- Harga beli
- Harga jual
- Stok minimum
- Status aktif/nonaktif

### FR-PROD-003 [P0]
SKU harus unik.

### FR-PROD-004 [P0]
Produk dapat diedit sesuai hak akses.

### FR-PROD-005 [P0]
Produk dapat dinonaktifkan tanpa menghapus histori transaksi.

### FR-PROD-006 [P0]
Produk dapat dicari berdasarkan SKU, barcode, atau nama.

## 4. Kategori & Satuan

### FR-MASTER-001 [P0]
Admin dapat CRUD kategori.

### FR-MASTER-002 [P0]
Admin dapat CRUD satuan.

### FR-MASTER-003 [P0]
Kategori/satuan yang masih digunakan produk tidak boleh dihapus secara sembarangan.

## 5. Supplier

### FR-SUP-001 [P0]
Admin/Gudang dapat menambah supplier.

### FR-SUP-002 [P0]
Supplier memiliki nama, kontak, alamat, dan status.

### FR-SUP-003 [P0]
Supplier dapat dinonaktifkan tanpa menghapus histori penerimaan.

### FR-SUP-004 [P0]
Admin/Gudang dapat mengubah data supplier (nama, kontak, alamat, kode, status).

### FR-SUP-005 [P0]
Daftar supplier dapat dicari (nama/kode) dan difilter berdasarkan status aktif/nonaktif.

### FR-SUP-006 [P1]
Kode supplier bersifat opsional dan unik bila diisi.

## 6. Inventory

### FR-STOCK-001 [P0]
Sistem menyimpan stok saat ini setiap produk.

### FR-STOCK-002 [P0]
Penerimaan barang menambah stok.

### FR-STOCK-003 [P0]
Penjualan mengurangi stok.

### FR-STOCK-004 [P0]
Sistem menolak transaksi penjualan apabila stok tidak mencukupi.

### FR-STOCK-005 [P0]
Penyesuaian stok harus menyimpan alasan.

### FR-STOCK-006 [P0]
Setiap perubahan stok memiliki stock movement/history.

### FR-STOCK-007 [P0]
Sistem dapat menampilkan produk yang mencapai atau berada di bawah stok minimum.

## 7. Penerimaan/Pembelian

### FR-PURCHASE-001 [P0]
Petugas Gudang dapat membuat penerimaan barang dari supplier.

### FR-PURCHASE-002 [P0]
Penerimaan memiliki nomor dokumen unik.

### FR-PURCHASE-003 [P0]
Penerimaan memiliki detail produk, qty, harga beli, dan subtotal.

### FR-PURCHASE-004 [P0]
Penerimaan yang berhasil meningkatkan stok secara atomik.

### FR-PURCHASE-005 [P0]
Penerimaan final tidak boleh diedit sembarangan.

## 8. Penjualan

### FR-SALE-001 [P0]
Kasir dapat membuat transaksi penjualan.

### FR-SALE-002 [P0]
Kasir dapat mencari produk berdasarkan SKU/barcode/nama.

### FR-SALE-003 [P0]
Kasir dapat menambahkan produk ke cart transaksi.

### FR-SALE-004 [P0]
Sistem menghitung subtotal setiap item.

### FR-SALE-005 [P0]
Sistem menghitung grand total.

### FR-SALE-006 [P0]
Sistem memvalidasi stok sebelum transaksi final.

### FR-SALE-007 [P0]
Transaksi final mengurangi stok secara atomik.

### FR-SALE-008 [P0]
Sistem menyimpan metode pembayaran.

### FR-SALE-009 [P0]
Sistem menghitung jumlah pembayaran dan kembalian untuk pembayaran tunai.

### FR-SALE-010 [P0]
Nomor transaksi penjualan unik.

### FR-SALE-011 [P1]
Cetak/print-friendly receipt.

## 9. Dashboard

### FR-DASH-001 [P0]
Dashboard menampilkan ringkasan penjualan.

### FR-DASH-002 [P0]
Dashboard menampilkan produk dengan stok minimum.

### FR-DASH-003 [P0]
Dashboard menampilkan ringkasan jumlah produk aktif.

### FR-DASH-004 [P1]
Dashboard menampilkan grafik penjualan berdasarkan periode.

## 10. Laporan

### FR-REPORT-001 [P0]
Laporan penjualan dapat difilter berdasarkan periode.

### FR-REPORT-002 [P0]
Laporan stok menampilkan stok terkini.

### FR-REPORT-003 [P0]
Laporan stock movement dapat difilter berdasarkan produk/periode.

### FR-REPORT-004 [P1]
Laporan penjualan dapat diekspor.

## 11. Auditability

### FR-AUDIT-001 [P0]
Transaksi final menyimpan user yang membuat transaksi.

### FR-AUDIT-002 [P0]
Perubahan stok dapat ditelusuri ke sumbernya.

### FR-AUDIT-003 [P1]
Aktivitas administratif penting dicatat.

## 12. Non-Functional Requirements

### NFR-001 Security [P0]
Gunakan mekanisme keamanan Laravel dan hindari SQL injection, mass assignment yang tidak terkendali, serta XSS.

### NFR-002 Data Integrity [P0]
Operasi yang mengubah beberapa tabel dan harus berhasil bersama-sama menggunakan database transaction.

### NFR-003 Maintainability [P0]
Business logic utama berada di Service Layer.

### NFR-004 Separation of Concerns [P0]
Repository bertanggung jawab terhadap akses/pengambilan data; Service bertanggung jawab terhadap business process.

### NFR-005 Performance [P0]
Query list menggunakan pagination ketika datanya berpotensi besar.

### NFR-006 Usability [P0]
Halaman kasir meminimalkan jumlah langkah yang tidak perlu.

### NFR-007 Testability [P0]
Business logic kritis memiliki automated tests.

### NFR-008 Reliability [P0]
Kegagalan transaksi tidak boleh meninggalkan perubahan stok parsial.

### NFR-009 Accessibility [P0]
Frontend memenuhi standar aksesibilitas WCAG 2.2 level AA: kontras teks ≥ 4.5:1 dan non-teks ≥ 3:1, seluruh fungsi dapat dioperasikan keyboard dengan indikator fokus terlihat, setiap input memiliki label programatik, pesan error teridentifikasi dan terhubung ke input terkait, konten dapat di-resize 200% tanpa kehilangan fungsi, serta penggunaan bahasa halaman dan semantic HTML yang benar. Lihat prinsip aksesibilitas di `03-product-spec.md`.

### NFR-010 Document Language [P0]
Seluruh halaman menyatakan bahasa dokumen dengan benar melalui atribut `lang` agar teknologi bantu (screen reader) dapat membaca konten dengan tepat.
