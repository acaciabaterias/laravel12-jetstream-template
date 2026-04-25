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

    echo "[entrypoint] Executando migrations fundacionais da aplicacao..."
    php artisan migrate --database=central --path=database/migrations/0001_01_01_000000_create_users_table.php --force --no-interaction
    php artisan migrate --database=central --path=database/migrations/0001_01_01_000001_create_cache_table.php --force --no-interaction
    php artisan migrate --database=central --path=database/migrations/0001_01_01_000002_create_jobs_table.php --force --no-interaction
    php artisan migrate --database=central --path=database/migrations/2025_08_29_233452_add_two_factor_columns_to_users_table.php --force --no-interaction
    php artisan migrate --database=central --path=database/migrations/2025_08_29_233500_create_personal_access_tokens_table.php --force --no-interaction
    php artisan migrate --database=central --path=database/migrations/2025_08_29_233500_create_teams_table.php --force --no-interaction
    php artisan migrate --database=central --path=database/migrations/2025_08_29_233501_create_team_user_table.php --force --no-interaction
    php artisan migrate --database=central --path=database/migrations/2025_08_29_233502_create_team_invitations_table.php --force --no-interaction
    php artisan migrate --database=central --path=database/migrations/2026_02_19_135947_create_features_table.php --force --no-interaction
fi

echo "[entrypoint] Inicializacao concluida."

exec "$@"
