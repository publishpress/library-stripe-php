#!/usr/bin/env bash
# Linux Docker bind-mounts keep container UIDs. Mac Docker Desktop remaps them,
# so this is a no-op on Darwin.
#
# WPLoader runs on the host and needs write access to wp_test (uid 33 / www-data).
# Fixture plugins are copied into wp-content/plugins, so that tree is included.
set -euo pipefail

if [[ "$(uname -s)" != "Linux" ]]; then
    exit 0
fi

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$ROOT"

if [[ ! -f .env ]]; then
    exit 0
fi

set -a
# shellcheck disable=SC1091
source .env
set +a

if ! docker info >/dev/null 2>&1; then
    exit 0
fi

WP_CONTAINER="${CONTAINER_NAME:-library-stripe-php}_env_wp_test"
DB_CONTAINER="${CONTAINER_NAME:-library-stripe-php}_env_db_test"
HOST_OWNER="$(id -u):$(id -g)"

if ! docker ps -q --filter "name=${WP_CONTAINER}" | grep -q .; then
    exit 0
fi

docker exec -u root "$WP_CONTAINER" chown -R "${HOST_OWNER}" /var/www/html

if docker ps -q --filter "name=${DB_CONTAINER}" | grep -q .; then
    docker exec -u root "$DB_CONTAINER" chown -R mysql:mysql /var/lib/mysql
fi
