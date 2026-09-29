#!/usr/bin/env bash

set -Eeuo pipefail

APP_ROOT="${APP_ROOT:-/var/www/inventory}"
RELEASES_DIR="${RELEASES_DIR:-$APP_ROOT/releases}"
SHARED_DIR="${SHARED_DIR:-$APP_ROOT/shared}"
CURRENT_LINK="${CURRENT_LINK:-$APP_ROOT/current}"
TARGET_RELEASE="${1:-}"
MAINTENANCE_ENABLED=0

cleanup() {
    local exit_code=$?

    if [[ "$MAINTENANCE_ENABLED" == "1" && -x "$CURRENT_LINK/artisan" ]]; then
        php "$CURRENT_LINK/artisan" up || printf 'Warning: failed to disable maintenance mode.\n' >&2
    fi

    return "$exit_code"
}
trap cleanup EXIT

if [[ "$(id -u)" -eq 0 ]]; then
    printf 'Do not run this script as root.\n' >&2
    exit 77
fi

if [[ -z "$TARGET_RELEASE" ]]; then
    mapfile -t releases < <(find "$RELEASES_DIR" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %p\n' | sort -rn | cut -d' ' -f2-)
    CURRENT_RELEASE="$(readlink "$CURRENT_LINK")"

    for release in "${releases[@]}"; do
        if [[ "$release" != "$CURRENT_RELEASE" ]]; then
            TARGET_RELEASE="$release"
            break
        fi
    done
fi

if [[ -z "$TARGET_RELEASE" || ! -x "$TARGET_RELEASE/artisan" ]]; then
    printf 'No valid rollback release found.\n' >&2
    exit 66
fi

if [[ ! -f "$SHARED_DIR/.env" ]]; then
    printf 'Missing production environment: %s/.env\n' "$SHARED_DIR" >&2
    exit 66
fi

if [[ -x "$CURRENT_LINK/artisan" ]]; then
    php "$CURRENT_LINK/artisan" down --retry=60 --refresh=5
    MAINTENANCE_ENABLED=1
fi

TEMP_LINK="$APP_ROOT/.current.$$.tmp"
ln -s "$TARGET_RELEASE" "$TEMP_LINK"
mv -Tf "$TEMP_LINK" "$CURRENT_LINK"
php "$CURRENT_LINK/artisan" up
MAINTENANCE_ENABLED=0

# Lihat catatan yang sama di scripts/deploy-release.sh mengenai worker.
if [[ "${QUEUE_WORKER_ENABLED:-0}" == "1" ]]; then
    php "$CURRENT_LINK/artisan" queue:restart
fi

printf 'Rolled back to %s\n' "$TARGET_RELEASE"
