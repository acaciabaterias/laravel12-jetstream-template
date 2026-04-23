#!/usr/bin/env sh

set -eu

echo "[entrypoint] Validando ambiente..."
/var/www/html/validate-env.sh

mkdir -p \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

if [ -n "${DB_CENTRAL_HOST:-}" ]; then
    echo "[entrypoint] Aguardando PostgreSQL central em ${DB_CENTRAL_HOST}:${DB_CENTRAL_PORT:-5432}..."

    until pg_isready \
        -h "${DB_CENTRAL_HOST}" \
        -p "${DB_CENTRAL_PORT:-5432}" \
        -U "${DB_CENTRAL_USERNAME:-postgres}" >/dev/null 2>&1; do
        sleep 2
    done
fi

php artisan storage:link --no-interaction >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "[entrypoint] Executando migrations centrais..."
    php artisan migrate --database=central --path=database/migrations/central --force --no-interaction
fi

echo "[entrypoint] Inicializacao concluida."

exec "$@"
