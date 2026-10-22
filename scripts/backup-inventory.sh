#!/usr/bin/env bash
#
# Backup harian Sistem Inventori & Penjualan.
# Referensi: docs/11-backup-restore.md
#
# Menyalin database (mysqldump), storage publik, dan .env ke BACKUP_DIR,
# mengenkripsinya dengan age, membuat checksum SHA-256, lalu menerapkan
# retensi 14 hari. Exit non-zero bila salah satu langkah gagal.
#
# Variable yang wajib disediakan secret manager:
#   DB_DATABASE, DB_USERNAME, DB_PASSWORD, AGE_RECIPIENT
# Opsional: DB_HOST (default 127.0.0.1), APP_DIR, BACKUP_DIR
#
# Contoh crontab (02:00 setiap hari):
#   00 02 * * * DB_DATABASE=... DB_USERNAME=... DB_PASSWORD=... AGE_RECIPIENT=... /usr/local/bin/backup-inventory.sh
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/inventory}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/inventory}"
STAMP="$(date +%Y%m%d-%H%M%S)"

# Kredensial database harus disediakan melalui secret manager atau credential
# file sementara dengan permission 0600; jangan menaruh password di command line.
: "${DB_DATABASE:?DB_DATABASE must be provided by the secret manager}"
: "${DB_USERNAME:?DB_USERNAME must be provided by the secret manager}"
: "${DB_PASSWORD:?DB_PASSWORD must be provided by the secret manager}"
: "${AGE_RECIPIENT:?AGE_RECIPIENT must be provided by the secret manager}"

mkdir -p "$BACKUP_DIR"
MYSQL_CNF="$(mktemp)"
trap 'rm -f "$MYSQL_CNF"' EXIT
umask 077
cat > "$MYSQL_CNF" <<EOF
[client]
host=${DB_HOST:-127.0.0.1}
user=${DB_USERNAME}
password=${DB_PASSWORD}
EOF

mysqldump --single-transaction --routines --triggers --hex-blob \
  --defaults-extra-file="$MYSQL_CNF" "$DB_DATABASE" \
  | gzip > "$BACKUP_DIR/inventory-$STAMP.sql.gz"

tar -czf "$BACKUP_DIR/storage-$STAMP.tar.gz" -C "$APP_DIR/storage/app/public" .
age --encrypt --recipient "$AGE_RECIPIENT" \
  --output "$BACKUP_DIR/inventory-$STAMP.sql.gz.age" \
  "$BACKUP_DIR/inventory-$STAMP.sql.gz"
rm -f "$BACKUP_DIR/inventory-$STAMP.sql.gz"
age --encrypt --recipient "$AGE_RECIPIENT" \
  --output "$BACKUP_DIR/env-$STAMP.env.age" \
  "$APP_DIR/.env"

sha256sum "$BACKUP_DIR"/*-"$STAMP".* > "$BACKUP_DIR/SHA256SUMS-$STAMP"

find "$BACKUP_DIR" -name 'inventory-*.sql.gz.age' -mtime +14 -delete
find "$BACKUP_DIR" -name 'storage-*.tar.gz'   -mtime +14 -delete
find "$BACKUP_DIR" -name 'env-*.env.age'      -mtime +14 -delete
find "$BACKUP_DIR" -name 'SHA256SUMS-*'       -mtime +14 -delete
