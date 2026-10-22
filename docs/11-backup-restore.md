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

Jangan menaruh password pada argument `mysqldump`/`mysql` karena dapat terlihat
di process list atau audit shell. Gunakan credential file sementara dengan
permission `0600`, lalu hapus setelah proses selesai:

```sh
# Lokasi backup
mkdir -p /var/backups/inventory
umask 077
MYSQL_CNF="$(mktemp)"
trap 'rm -f "$MYSQL_CNF"' EXIT

cat > "$MYSQL_CNF" <<EOF
[client]
host=<DB_HOST>
user=<DB_USERNAME>
password=<DB_PASSWORD>
EOF

mysqldump \
  --single-transaction --routines --triggers --hex-blob \
  --defaults-extra-file="$MYSQL_CNF" <DB_DATABASE> \
  | gzip > /var/backups/inventory/inventory-$(date +%Y%m%d-%H%M%S).sql.gz
```

- `--single-transaction` → snapshot konsisten tanpa mengunci tabel InnoDB selama dump (aplikasi tetap bisa menulis).
- `--hex-blob` → data biner (bila ada kolom BLOB) aman diketikkan.
- File hasil dump dan arsip rahasia harus dienkripsi sebelum meninggalkan server.
- Simpan backup terenkripsi di **server/lokasi yang berbeda** dari server produksi (off-site), misalnya object storage dengan server-side encryption dan akses write-only dari host produksi.
- Simpan checksum bersama setiap artefak dan verifikasi checksum sebelum restore.

## 3. Backup File Storage & Env

Storage publik dapat diarsipkan tanpa enkripsi jika memang tidak mengandung
data rahasia. `.env` dan dump database wajib dienkripsi dengan kunci yang
disimpan di secret manager atau host backup, bukan di repository/server aplikasi.

```sh
# Storage publik (foto produk)
tar -czf /var/backups/inventory/storage-$(date +%Y%m%d-%H%M%S).tar.gz \
  -C /var/www/inventory/storage/app/public .

# Env (rahasia produksi): contoh memakai age.
# AGE_RECIPIENT harus diberikan dari secret manager atau environment service.
age --encrypt --recipient "$AGE_RECIPIENT" \
  --output "/var/backups/inventory/env-$(date +%Y%m%d-%H%M%S).env.age" \
  /var/www/inventory/.env

# Enkripsi dump database dan hapus plaintext setelah sukses.
age --encrypt --recipient "$AGE_RECIPIENT" \
  --output "/var/backups/inventory/inventory-<STAMP>.sql.gz.age" \
  "/var/backups/inventory/inventory-<STAMP>.sql.gz"
rm -f "/var/backups/inventory/inventory-<STAMP>.sql.gz"

# Rotasi: simpan N hari terakhir
find /var/backups/inventory -name 'inventory-*.sql.gz.age' -mtime +14 -delete
find /var/backups/inventory -name 'storage-*.tar.gz' -mtime +14 -delete
find /var/backups/inventory -name 'env-*.env.age'     -mtime +14 -delete
```

## 4. Backup Otomatis (cron)

Script backup otomatis tersedia di versi kontrol: `scripts/backup-inventory.sh`.
Satu script menggabungkan ketiga proses: dump database (credential file 0600,
tanpa password di command line), archive `storage/app/public`, enkripsi `age`,
checksum SHA-256, dan rotasi retensi 14 hari. Script exit non-zero bila
enkripsi atau checksum gagal.

Pasang di server:

```sh
sudo install -m 0750 scripts/backup-inventory.sh /usr/local/bin/backup-inventory.sh
```

Jadwalkan di crontab user yang punya akses, misal `00 02 * * *` (setiap 02:00),
dengan `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, dan `AGE_RECIPIENT` disediakan
oleh secret manager:

```cron
00 02 * * * DB_DATABASE=... DB_USERNAME=... DB_PASSWORD=... AGE_RECIPIENT=... /usr/local/bin/backup-inventory.sh
```

Jangan menghapus backup lokal sebelum artefak terenkripsi dan checksum berhasil
dibuat. Upload harian ke lokasi off-site harus memakai transport terenkripsi dan
credential write-only.

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

# 3. Verifikasi checksum dan decrypt environment ke file dengan permission 0600
sha256sum --check <backup>/SHA256SUMS-<STAMP>
age --decrypt --output .env <backup>/env-<STAMP>.env.age
chmod 600 .env

# Credential file sementara untuk import database (permission 0600)
MYSQL_CNF="$(mktemp)"
trap 'rm -f "$MYSQL_CNF"' EXIT
umask 077
cat > "$MYSQL_CNF" <<EOF
[client]
host=<DB_HOST>
user=<DB_USERNAME>
password=<DB_PASSWORD>
EOF

# 4. Symlink storage
php artisan storage:link

# 5. Storage (foto produk)
tar -xzf <backup>/storage-<STAMP>.tar.gz -C storage/app/public

# 6. Database (dari dump terenkripsi)
age --decrypt <backup>/inventory-<STAMP>.sql.gz.age \
  | gunzip \
  | mysql --defaults-extra-file="$MYSQL_CNF" <DB_DATABASE>

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
- Enkripsi backup yang berisi `.env` dan database **sebelum** dikirim off-site.
- Simpan kunci dekripsi secara terpisah dari backup dan host aplikasi; uji proses pengambilan kunci saat restore drill.
- Untuk volume kecil (aplikasi ini), dump + tar harian sudah cukup — tidak perlu binlog/replica formal kecuali kebutuhan RPO/RTO lebih ketat.