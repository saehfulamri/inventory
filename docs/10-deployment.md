# Deployment — Panduan Menuju Produksi

Dokumen ini berisi langkah deploy aplikasi ke server produksi. Aplikasi adalah monolith Laravel, sehingga deployment cukup ringan: satu web server + PHP-FPM + MySQL. Queue worker belum diaktifkan karena belum ada job asynchronous — lihat bagian 8 untuk keputusan dan kriteria aktivasinya.

## 1. Persyaratan Server

- PHP >= 8.4 dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `gd` *(jika menjalankan fitur foto produk di masa depan)*.
- Composer 2.
- MySQL 8+ / MariaDB 10.6+.
- Node.js 20+ dan npm (untuk membangun aset frontend Vue/Inertia + Tailwind saat rilis).
- Nginx atau Caddy (disarankan) / Apache.
- Akses SSH + kemampuan install system service.

DNS sudah diarahkan ke server dan sertifikat TLS tersedia (Caddy/LetsEncrypt) — traffic produksi **harus HTTPS**.

## 2. Langkah Deploy (Server Baru)

Server baru mengikuti alur GitHub Flow yang sama: clone repository sebagai
`repository`, lalu deploy tag rilis melalui script release atomik. Jangan
checkout branch fitur atau melakukan build langsung di document root.

```sh
# 1. Siapkan clone yang hanya menjadi sumber tag/commit
git clone --no-checkout git@github.com:saehfulamri/inventory.git \
  /var/www/inventory/repository
cd /var/www/inventory/repository
git fetch --tags --prune

# 2. Environment produksi (sekali saja, sebelum deployment pertama)
sudo install -o deploy -g www-data -m 0600 /path/to/production/.env \
  /var/www/inventory/shared/.env

# 3. Deploy tag yang sudah dibuat dari commit main setelah PR merge
cd /var/www/inventory/repository
HEALTH_URL=https://inventory.example.com/up \
  ./scripts/deploy-release.sh v0.3.0
```

### 2.1 Isi `.env` produksi

```dotenv
APP_NAME="Sistem Inventori & Penjualan"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://inventory.example.com

APP_LOCALE=en          # UI memakai teks Indonesia hardcoded; framework tanpa lang/id, jadi biarkan en agar pesan sistem tidak jadi raw key

LOG_CHANNEL=daily          # rotasi harian, bukan single
LOG_LEVEL=info             # jangan debug di produksi

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory
DB_USERNAME=_isi_
DB_PASSWORD=_isi_           # gunakan kredensial terpisah, bukan root

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true  # hanya kirim cookie via HTTPS
SESSION_LIFETIME=120

CACHE_STORE=database
QUEUE_CONNECTION=database   # driver siap pakai; worker belum diaktifkan, lihat bagian 8

MAIL_MAILER=log             # sesuaikan bila ingin email sungguhan
```

> ⚠️ Jangan pernah menjalankan `php artisan db:seed` di produksi — `DatabaseSeeder` hanya men-seed data demo saat `APP_ENV=local|testing`.

### 2.2 Migrasi, storage, dan aset

Migrasi, storage link, dependency install, build asset, dan cache dijalankan
oleh `scripts/deploy-release.sh` di release baru. Jangan menjalankan langkah
tersebut manual di `current` karena dapat mencampur dua versi aplikasi.

```sh
# Rights — pastikan user web server dapat menulis
sudo chown -R deploy:www-data /var/www/inventory/shared/storage
sudo chmod -R u+rwX,g+rwX /var/www/inventory/shared/storage
```

`view:cache` hanya mencakup root template Blade (`app.blade.php`); halaman Inertia dirender di browser dari bundel `public/build/` hasil `npm run build`.

Perintah cache perlu diulang **setiap kali** kode atau konfigurasi berubah di proses update (lihat bagian 4).

## 3. Konfigurasi Web Server

### Nginx (contoh)

```nginx
# Redirect seluruh traffic HTTP ke HTTPS.
server {
    listen 80;
    listen [::]:80;
    server_name inventory.example.com;

    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name inventory.example.com;

    root /var/www/inventory/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/inventory.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/inventory.example.com/privkey.pem;

    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Foto produk & aset statis
    location ~* \.(jpg|jpeg|png|webp)$ {
        expires 30d;
        add_header Cache-Control "public, immutable" always;
        add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
        add_header X-Content-Type-Options "nosniff" always;
        add_header X-Frame-Options "SAMEORIGIN" always;
        add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }

    # Jangan pernah expose file tersembunyi atau file konfigurasi.
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Setelah mengaktifkan konfigurasi, validasi dan reload secara eksplisit:

```sh
sudo nginx -t
sudo systemctl reload nginx
curl -I http://inventory.example.com/
curl -I https://inventory.example.com/
```

Request HTTP harus mengembalikan `301` menuju HTTPS dan response HTTPS harus
memuat header `Strict-Transport-Security`. HSTS hanya boleh diaktifkan setelah
sertifikat TLS dan seluruh subdomain yang tercakup benar-benar siap HTTPS.

### Caddy (lebih sederhana)

```caddy
inventory.example.com {
    root * /var/www/inventory/public
    php_fastcgi unix//run/php/php8.4-fpm.sock
    encode gzip
    header {
        Strict-Transport-Security "max-age=31536000; includeSubDomains"
        X-Content-Type-Options "nosniff"
        X-Frame-Options "SAMEORIGIN"
        Referrer-Policy "strict-origin-when-cross-origin"
    }
    handle_errors {
        rewrite * /index.php {query}
        php_fastcgi unix//run/php/php8.4-fpm.sock
    }
}
```

## 4. Deployment Release Atomik

Gunakan layout berikut agar release baru dibangun tanpa mengubah release yang
sedang aktif:

```text
/var/www/inventory/
├── current -> releases/<release-id>
├── repository/                 # clone Git dengan remote origin
├── releases/<release-id>/     # immutable setelah aktif
└── shared/
    ├── .env
    └── storage/
```

Web server harus menunjuk ke `/var/www/inventory/current/public`, bukan ke
direktori release tertentu. Siapkan direktori dan environment sekali:

```sh
sudo install -d -o deploy -g www-data -m 0750 \
  /var/www/inventory/{repository,releases,shared,shared/storage}
sudo install -d -o deploy -g www-data -m 0750 /var/www/inventory/shared/storage/app
sudo install -o deploy -g www-data -m 0600 /path/to/production/.env \
  /var/www/inventory/shared/.env
git clone git@github.com:saehfulamri/inventory.git /var/www/inventory/repository
```

Tambahkan script deployment ke repository dan jadikan executable:

```sh
chmod 0750 scripts/deploy-release.sh scripts/rollback-release.sh
```

Jalankan deployment sebagai user `deploy`, bukan `root`. Script membangun
dependency dan asset di release baru, membuat cache, mengaktifkan maintenance
mode pada release aktif, menjalankan migrasi, lalu mengganti symlink `current`
secara atomik. Migrasi harus backward-compatible dengan release aktif karena
database berubah sebelum symlink berpindah.

```sh
cd /var/www/inventory/repository
git fetch --tags --prune

cd /var/www/inventory
HEALTH_URL=https://inventory.example.com/up \
  repository/scripts/deploy-release.sh v0.3.0
```

`HEALTH_URL` harus mengarah ke endpoint yang hanya dianggap sehat jika response
HTTP berhasil. Jika preflight, build, cache, atau migrasi gagal sebelum switch,
release baru dihapus dan release aktif tetap dipertahankan. Jika health check
gagal setelah switch, lakukan rollback segera; script tidak melakukan rollback
otomatis karena migrasi mungkin sudah mengubah schema.

## 5. Rollback Release

Rollback hanya mengganti symlink ke release sebelumnya. Jangan otomatis
menjalankan `migrate:rollback`, karena data mungkin sudah ditulis memakai
schema baru. Gunakan migrasi forward-compatible untuk memperbaiki deployment
yang gagal.

```sh
cd /var/www/inventory
scripts/rollback-release.sh
```

Target release juga dapat diberikan secara eksplisit:

```sh
scripts/rollback-release.sh /var/www/inventory/releases/20260925020000
```

Setelah deployment atau rollback, verifikasi:

```sh
readlink /var/www/inventory/current
curl --fail --silent --show-error https://inventory.example.com/up
php /var/www/inventory/current/artisan about
php /var/www/inventory/current/artisan migrate:status
```

Simpan minimal lima release terakhir. Hapus release lama hanya setelah health
check release baru berhasil dan backup telah tervalidasi.

## 6. Checklist Keamanan Sebelum Go-Live

- [ ] `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] Kredensial DB non-root dengan hak terbatas.
- [ ] HTTPS aktif dan cookie session `SESSION_SECURE_COOKIE=true`.
- [ ] `php artisan config:cache` dsb. sudah dijalankan.
- [ ] Foto produk dapat diakses via `APP_URL/storage/...`.
- [ ] Backup otomatis terjadwal (lihat `11-backup-restore.md`).
- [ ] Restore backup pernah diuji di server/pulau lain.
- [ ] App key unik (`APP_KEY` hasil generate, bukan dari repor).
- [ ] Queue worker: tidak diaktifkan kecuali kriteria bagian 8.1 terpenuhi.

## 7. Operasional Harian

```sh
php artisan about              # cek environment/config ter-cache
php artisan migrate:status     # status migrasi
php artisan storage:link       # pastikan symlink ada
tail -f storage/logs/laravel.log
```

> Tidak ada cron scheduler yang wajib dijalankan untuk fungsionalitas saat ini.
> Untuk queue worker, lihat bagian 8.

## 8. Operasi Queue Produksi

### 8.1 Keputusan saat ini: tetap sinkron, tanpa worker

Per penulisan dokumen ini, **tidak ada job yang mengimplementasikan
`ShouldQueue`** dan tidak ada pemanggilan `Bus::dispatch()`/`::dispatch()` di
`app/`. Semua proses (import/export, notifikasi, dsb.) berjalan sinkron dalam
request HTTP. `QUEUE_CONNECTION=database` tetap diset sebagai driver cadangan
(tabel `jobs` dan `failed_jobs` sudah ada dari migrasi bawaan Laravel), tetapi
**tidak ada worker (`queue:work`) yang dijalankan di produksi**, sehingga tidak
relevan untuk memantau proses worker yang menumpuk job.

**Kriteria kapan worker perlu diaktifkan** — aktifkan queue worker sebelum job
apa pun di-deploy ke produksi jika salah satu berikut terjadi:

- Ada job baru yang mengimplementasikan `ShouldQueue` (mis. pengiriman email
  notifikasi, generate laporan besar, import/export data massal).
- Ada proses yang saat ini sinkron dipindah ke background untuk menghindari
  timeout request (mis. proses > beberapa detik).
- Sebuah package pihak ketiga secara default men-dispatch job ke queue.

Jika kriteria di atas belum terpenuhi, **jangan** menjalankan `queue:work` di
produksi — worker yang berjalan tanpa job untuk diproses hanya menghabiskan
resource dan menambah permukaan operasional tanpa manfaat.

### 8.2 Rencana aktivasi (jika/ketika dibutuhkan)

1. **Pilih koneksi queue.** Gunakan `database` (driver saat ini) untuk volume
   job rendah–menengah tanpa dependency tambahan. Pertimbangkan `redis` hanya
   jika throughput tinggi atau butuh delay/priority queue yang lebih efisien —
   ini menambah dependency infra (server Redis) sehingga perlu keputusan
   terpisah, bukan default.
2. **Jalankan worker via Supervisor** (disarankan) menggunakan template di
   `deploy/supervisor/inventory-worker.conf.example`. Salin ke
   `/etc/supervisor/conf.d/inventory-worker.conf`, sesuaikan path/user, lalu:
   ```sh
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start "inventory-worker:*"
   ```
   Alternatif systemd: buat unit `inventory-worker@.service` dengan
   `Restart=always`, `ExecStart=php /var/www/inventory/current/artisan queue:work ...`,
   dan `WorkingDirectory=/var/www/inventory/current`.
3. **Timeout, retry, dan `retry_after`.** Gunakan opsi CLI eksplisit, jangan
   andalkan default:
   - `--tries=3` — job gagal permanen dipindah ke `failed_jobs` setelah 3
     percobaan (gunakan `$job->tries` per job untuk override jika perlu).
   - `--timeout=90` — proses child worker dibunuh jika job berjalan > 90 detik
     (job harus dirancang idempoten agar aman diulang).
   - `retry_after` (di `config/queue.php`, env `DB_QUEUE_RETRY_AFTER`, default
     90 detik) **harus lebih besar** dari `--timeout` supaya job yang masih
     berjalan tidak dianggap gagal dan diambil ulang oleh worker lain.
   - `after_commit` job database queue sudah `false` secara default
     (`config/queue.php`) — job baru yang perlu menunggu transaksi DB commit
     dahulu (agar tidak race dengan data yang belum tersimpan) harus eksplisit
     set `$job->afterCommit = true` atau gunakan trait
     `Illuminate\Bus\Batchable`/`ShouldBeAfterCommit` sesuai kebutuhan.
4. **Restart worker saat deploy.** `scripts/deploy-release.sh` dan
   `scripts/rollback-release.sh` sudah menyediakan hook: set
   `QUEUE_WORKER_ENABLED=1` saat menjalankan script agar `artisan queue:restart`
   dipanggil setelah simlink `current` berpindah. Perintah ini memberi sinyal
   graceful ke worker agar keluar setelah job berjalan selesai; Supervisor
   (`autorestart=true`) akan menyalakannya kembali memakai kode rilis
   terbaru. **Jangan** `kill -9` proses worker — itu bisa memotong job di
   tengah eksekusi.
5. **Pantau `failed_jobs` dan sediakan prosedur triage:**
   ```sh
   php artisan queue:failed                 # daftar job gagal
   php artisan queue:retry <id|--all>       # retry job tertentu/semua
   php artisan queue:forget <id>            # buang job gagal setelah ditinjau
   php artisan queue:prune-failed --hours=720   # bersihkan job gagal lama (>30 hari)
   ```
   Jadwalkan `queue:prune-failed` mingguan (lihat `05-database.md`/scheduler)
   agar tabel `failed_jobs` tidak tumbuh tanpa batas. Alert operasional untuk
   `failed_jobs` dijelaskan di `12-copilot_infrastructure-remediation-plan.md`
   Fase 5 (observability).

### 8.3 Checklist sebelum mengaktifkan worker di produksi

- [ ] Job baru sudah diuji idempoten (aman dijalankan ulang setelah retry).
- [ ] `--timeout` worker < `retry_after` koneksi queue.
- [ ] Supervisor/systemd worker terdaftar dengan `autorestart=true`.
- [ ] Hook `QUEUE_WORKER_ENABLED=1` diaktifkan di prosedur deploy.
- [ ] Prosedur monitoring/alert `failed_jobs` sudah berjalan (Fase 5).