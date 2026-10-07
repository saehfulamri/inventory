#!/usr/bin/env bash

set -Eeuo pipefail

APP_ROOT="${APP_ROOT:-/var/www/inventory}"
REPOSITORY="${REPOSITORY:-$APP_ROOT/repository}"
RELEASES_DIR="${RELEASES_DIR:-$APP_ROOT/releases}"
SHARED_DIR="${SHARED_DIR:-$APP_ROOT/shared}"
CURRENT_LINK="${CURRENT_LINK:-$APP_ROOT/current}"
RELEASE_REF="${1:-}"
RELEASE_ID="${RELEASE_ID:-$(date -u +%Y%m%d%H%M%S)}"
RELEASE_DIR="$RELEASES_DIR/$RELEASE_ID"
HEALTH_URL="${HEALTH_URL:-}"
KEEP_RELEASES="${KEEP_RELEASES:-5}"

if [[ -z "$RELEASE_REF" ]]; then
    printf 'Usage: %s <git-ref>\n' "$0" >&2
    exit 64
fi

if [[ "$(id -u)" -eq 0 ]]; then
    printf 'Do not run this script as root.\n' >&2
    exit 77
fi

if [[ ! -d "$REPOSITORY/.git" ]]; then
    printf 'Repository not found: %s\n' "$REPOSITORY" >&2
    exit 66
fi

if [[ ! -f "$SHARED_DIR/.env" ]]; then
    printf 'Missing production environment: %s/.env\n' "$SHARED_DIR" >&2
    exit 66
fi

if [[ -e "$RELEASE_DIR" ]]; then
    printf 'Release already exists: %s\n' "$RELEASE_DIR" >&2
    exit 73
fi

mkdir -p "$RELEASES_DIR" "$SHARED_DIR/storage/app"
git -C "$REPOSITORY" fetch --tags --prune
git -C "$REPOSITORY" rev-parse --verify "$RELEASE_REF^{commit}" >/dev/null
mkdir "$RELEASE_DIR"
git -C "$REPOSITORY" archive "$RELEASE_REF" | tar -x -C "$RELEASE_DIR"

cleanup() {
    local exit_code=$?

    if [[ "${MAINTENANCE_ENABLED:-0}" == "1" && -x "$CURRENT_LINK/artisan" ]]; then
        php "$CURRENT_LINK/artisan" up || printf 'Warning: failed to disable maintenance mode.\n' >&2
    fi

    if [[ -d "$RELEASE_DIR" && "${DEPLOY_SUCCEEDED:-0}" != "1" ]]; then
        rm -rf "$RELEASE_DIR"
    fi

    return "$exit_code"
}
trap cleanup EXIT

ln -s "$SHARED_DIR/.env" "$RELEASE_DIR/.env"
rm -rf "$RELEASE_DIR/storage"
ln -s "$SHARED_DIR/storage" "$RELEASE_DIR/storage"

(
    cd "$RELEASE_DIR"
    composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
    npm ci --ignore-scripts
    npm run build
    php artisan storage:link
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
)

CURRENT_APP="${CURRENT_LINK%/}"
if [[ -L "$CURRENT_LINK" ]]; then
    CURRENT_APP="$(readlink "$CURRENT_LINK")"
fi

if [[ -x "$CURRENT_APP/artisan" ]]; then
    php "$CURRENT_APP/artisan" down --retry=60 --refresh=5
    MAINTENANCE_ENABLED=1
fi

(
    cd "$RELEASE_DIR"
    php artisan migrate --force
)

TEMP_LINK="$APP_ROOT/.current.$$.tmp"
ln -s "$RELEASE_DIR" "$TEMP_LINK"
mv -Tf "$TEMP_LINK" "$CURRENT_LINK"
DEPLOY_SUCCEEDED=1

if [[ -x "$CURRENT_LINK/artisan" ]]; then
    php "$CURRENT_LINK/artisan" up
    MAINTENANCE_ENABLED=0
fi

# Queue worker tidak aktif secara default (lihat 10-deployment.md bagian 8).
# Jika Supervisor/systemd worker diaktifkan, set QUEUE_WORKER_ENABLED=1 agar
# worker lama keluar setelah job berjalan selesai dan Supervisor merestart-nya
# dengan kode rilis terbaru.
if [[ "${QUEUE_WORKER_ENABLED:-0}" == "1" && -x "$CURRENT_LINK/artisan" ]]; then
    php "$CURRENT_LINK/artisan" queue:restart
fi

if [[ -n "$HEALTH_URL" ]]; then
    curl --fail --silent --show-error --location --max-time 15 "$HEALTH_URL" >/dev/null
fi

mapfile -t old_releases < <(find "$RELEASES_DIR" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %p\n' | sort -rn | tail -n +"$((KEEP_RELEASES + 1))" | cut -d' ' -f2-)
if ((${#old_releases[@]} > 0)); then
    rm -rf -- "${old_releases[@]}"
fi

printf 'Activated release %s from %s\n' "$RELEASE_ID" "$RELEASE_REF"
