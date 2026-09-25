# Backup & Restore — Prosedur Operasional

Prosedur ini mencakup cadangan database (MySQL) dan file aplikasi + storage (terutama `storage/app/public/products` — foto produk) serta langkah pemulihan dengan urutan yang benar.

Prinsip:

- Backup = database + file (storage) + kode. Ketiganya harus diambil dalam kondisi konsisten.
- Restore = kode → env → storage → database, lalu jalankan ulang cache.
- Uji restore secara berkala ke *staging clone* — backup yang tidak pernah direstore tidak bisa dianggap aman.

## 1. Yang Wajib di-Backup

| Aset | Lokasi | Keterangan |
| --- | --- | --- |
| Database MySQL | *schema + data* | Sumber kebenaran semua data transaksi |
| Storage publik | `storage/app/public/` | Foto produk (`products/…`), dsb. |
| File konfigurasi | `.env` | Kredensial & rahasia produksi |
| Kode | repo git (tag/commit) | Dibuat lewat git — history **harus** di-push ke remote |

> Tidak perlu mem-backup `vendor/`, `node_modules/`, `public/build/` (hasil build aset frontend Vue/Inertia — regenerable via `npm run build`), atau `storage/framework/cache|views|sessions|logs` — semuanya regenerable.

## 2. Backup Database (mysqldump)

```sh
# Lokasi backup
mkdir -p /var/backups/inventory

mysqldump \
  --single-transaction --routines --triggers --hex-blob \
  -u <DB_USERNAME> -p'<DB_PASSWORD>' <DB_DATABASE> \
  | gzip > /var/backups/inventory/inventory-$(date +%Y%m%d-%H%M%S).sql.gz
```

- `--single-transaction` → snapshot konsisten tanpa mengunci tabel InnoDB selama dump (aplikasi tetap bisa menulis).
- `--hex-blob` → data biner (bila ada kolom BLOB) aman diketikkan.
- Simpan backup di **server/lokasi yang berbeda** dari server produksi (off-site), misal di-pull ke mesin backup atau object storage.

## 3. Backup File Storage & Env

```sh
# Storage publik (foto produk)
tar -czf /var/backups/inventory/storage-$(date +%Y%m%d-%H%M%S).tar.gz \
  -C /var/www/inventory/storage/app/public .

# Env (rahasia produksi)
tar -czf /var/backups/inventory/env-$(date +%Y%m%d-%H%M%S).tar.gz \
  -C /var/www/inventory .env

# Rotasi: simpan N hari terakhir
find /var/backups/inventory -name 'inventory-*.sql.gz' -mtime +14 -delete
find /var/backups/inventory -name 'storage-*.tar.gz' -mtime +14 -delete
find /var/backups/inventory -name 'env-*.tar.gz'     -mtime +14 -delete
```

## 4. Backup Otomatis (cron)

Contoh `00 02 * * *` (setiap 02:00) — gabungkan database, storage, dan env dalam satu skrip `backup.sh`, lalu simpan di `/usr/local/bin/backup-inventory.sh`:

```sh
#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/inventory"
BACKUP_DIR="/var/backups/inventory"
STAMP="$(date +%Y%m%d-%H%M%S)"

# .env di-source untuk kredensial DB (tanpa menuliskannya di skrip)
set -a; source "$APP_DIR/.env"; set +a

mysqldump --single-transaction --routines --triggers --hex-blob \
  -h "${DB_HOST:-127.0.0.1}" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" \
  | gzip > "$BACKUP_DIR/inventory-$STAMP.sql.gz"

tar -czf "$BACKUP_DIR/storage-$STAMP.tar.gz" -C "$APP_DIR/storage/app/public" .
tar -czf "$BACKUP_DIR/env-$STAMP.tar.gz"     -C "$APP_DIR" .env

find "$BACKUP_DIR" -name 'inventory-*.sql.gz' -mtime +14 -delete
find "$BACKUP_DIR" -name 'storage-*.tar.gz'   -mtime +14 -delete
find "$BACKUP_DIR" -name 'env-*.tar.gz'       -mtime +14 -delete
```

Jadwalkan di crontab user yang punya akses, dan idealkan **sinkronisasi harian ke lokasi off-site** (`rsync` ke mesin lain, atau upload ke object storage).

## 5. Prosedur Restore

Urutan restore: **kode → env → storage → database**. Restore database harus dilakukan setelah storage dikembalikan agar referensi path foto valid.

### 5.1 Restore penuh (disaster recovery, server baru)

```sh
# 1. Kode
cd /var/www/inventory
git fetch --tags
git checkout <commithash/tag>          # commit yang dipakai saat backup diambil

# 2. Dependensi
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci && npm run build

# 3. Environment
cp <backup>/env-<STAMP>.tar.gz . && tar -xzf env-<STAMP>.tar.gz   # .env kembali

# 4. Symlink storage
php artisan storage:link

# 5. Storage (foto produk)
tar -xzf <backup>/storage-<STAMP>.tar.gz -C storage/app/public

# 6. Database (dari dump)
gunzip -c <backup>/inventory-<STAMP>.sql.gz | mysql -u <DB_USERNAME> -p'<DB_PASSWORD>' <DB_DATABASE>

# 7. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Verifikasi
php artisan migrate:status    # harus match dengan versi kode
php artisan about
```

### 5.2 Restore partial

- **Hanya foto produk hilang**: cukup langkah 5.
- **Hanya data berubah**: restore dump, lalu pastikan versi kode sesuai dengan skema dump (migrasi setelah restore kemungkinan besar tidak diperlukan kecuali dump memang lama).

### 5.3 Verifikasi restore (wajib)

- Login berhasil dengan akun produksi.
- Halaman produk menampilkan foto (URL `/storage/products/…` → 200).
- Nomor nota penjualan/pembelian terbaru konsisten (tidak dobel).
- Total penjualan hari ini di dashboard masuk akal.

## 6. Titik Data Penting untuk Uji

Uji restore secara berkala ke *staging clone* dengan langkah 5.1, lalu bandingkan:

```sql
SELECT COUNT(*) FROM products;
SELECT COUNT(*) FROM sales;
SELECT MAX(id), MAX(created_at) FROM stock_movements;
```

## 7. Catatan

- Backup bukan jaminan keamanan: **gunanya adalah bisa direstore**. Schedule test restore minimal 1×/bulan.
- Enkripsi backup yang berisi `.env` bila dikirim off-site.
- Untuk volume kecil (aplikasi ini), dump + tar harian sudah cukup — tidak perlu binlog/replica formal kecuali kebutuhan RPO/RTO lebih ketat.