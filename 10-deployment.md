# Deployment — Panduan Menuju Produksi

Dokumen ini berisi langkah deploy aplikasi ke server produksi. Aplikasi adalah monolith Laravel, sehingga deployment cukup ringan: satu web server + PHP-FPM + MySQL, tanpa queue worker (semua proses saat ini berjalan sinkron).

## 1. Persyaratan Server

- PHP >= 8.4 dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `gd` *(jika menjalankan fitur foto produk di masa depan)*.
- Composer 2.
- MySQL 8+ / MariaDB 10.6+.
- Node.js 20+ dan npm (hanya untuk membangun aset frontend saat rilis).
- Nginx atau Caddy (disarankan) / Apache.
- Akses SSH + kemampuan install system service.

DNS sudah diarahkan ke server dan sertifikat TLS tersedia (Caddy/LetsEncrypt) — traffic produksi **harus HTTPS**.

## 2. Langkah Deploy (Server Baru)

```sh
# 1. Ambil kode
git clone git@github.com:saehfulamri/inventory.git /var/www/inventory
cd /var/www/inventory

# 2. Setel branch rilis/stable
git fetch --tags
git checkout v0.1.0   # atau tag/komit tertentu

# 3. Dependensi (tanpa tooling development)
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 4. Environment produksi
cp .env.example .env
php artisan key:generate
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
QUEUE_CONNECTION=database   # cadangan; saat ini belum ada job

MAIL_MAILER=log             # sesuaikan bila ingin email sungguhan
```

> ⚠️ Jangan pernah menjalankan `php artisan db:seed` di produksi — `DatabaseSeeder` hanya men-seed data demo saat `APP_ENV=local|testing`.

### 2.2 Migrasi, storage, dan aset

```sh
# Basis data
php artisan migrate --force

# Symlink storage/public (wajib agar foto produk di /storage/products/... bisa diakses)
php artisan storage:link

# Aset frontend (Tailwind/CSS build)
npm ci
npm run build

# Rights — pastikan user web server dapat menulis
sudo chown -R www-data:www-data storage bootstrap/cache

# Cache produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Perintah cache perlu diulang **setiap kali** kode atau konfigurasi berubah di proses update (lihat bagian 4).

## 3. Konfigurasi Web Server

### Nginx (contoh)

```nginx
server {
    listen 443 ssl http2;
    server_name inventory.example.com;

    root /var/www/inventory/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/inventory.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/inventory.example.com/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Foto produk & aset statis
    location ~* \.(jpg|jpeg|png|webp)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

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

## 4. Proses Update (Release)

```sh
cd /var/www/inventory

# 1. Backup dulu — lihat 11-backup-restore.md
# 2. Ambil versi baru
git fetch --tags
git checkout v0.2.0   # contoh versi berikutnya

# 3. Dependensi & aset (bila ada perubahan)
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci && npm run build

# 4. Migrasi (idempotent; aman dijalankan saat maintenance off-peak)
php artisan down --retry=60
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan up
```

Rollback cepat bila terjadi masalah: checkout kembali versi sebelumnya, ulangi langkah 3–4 (migrasi rollback tidak perlu otomatis — lakukan `php artisan migrate:rollback` hanya bila migrasi baru bermasalah dan data belum dipakai).

## 5. Checklist Keamanan Sebelum Go-Live

- [ ] `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] Kredensial DB non-root dengan hak terbatas.
- [ ] HTTPS aktif dan cookie session `SESSION_SECURE_COOKIE=true`.
- [ ] `php artisan config:cache` dsb. sudah dijalankan.
- [ ] Foto produk dapat diakses via `APP_URL/storage/...`.
- [ ] Backup otomatis terjadwal (lihat `11-backup-restore.md`).
- [ ] Restore backup pernah diuji di server/pulau lain.
- [ ] App key unik (`APP_KEY` hasil generate, bukan dari repor).

## 6. Operasional Harian

```sh
php artisan about              # cek environment/config ter-cache
php artisan migrate:status     # status migrasi
php artisan storage:link       # pastikan symlink ada
tail -f storage/logs/laravel.log
```

> Tidak ada queue worker / cron scheduler yang wajib dijalankan untuk fungsionalitas saat ini.