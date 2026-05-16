#!/usr/bin/env bash

set -euo pipefail

BACKUP_DIR="${BACKUP_DIR:-./backups}"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
DB_HOST="${DB_CENTRAL_HOST:-localhost}"
DB_PORT="${DB_CENTRAL_PORT:-5432}"
DB_NAME="${DB_CENTRAL_DATABASE:-erp_central}"
DB_USER="${DB_CENTRAL_USERNAME:-gil}"
DB_PASSWORD="${DB_CENTRAL_PASSWORD:-${PGPASSWORD:-}}"
TENANT_DB_HOST="${TENANT_DB_HOST:-}"
TENANT_DB_PORT="${TENANT_DB_PORT:-5432}"
TENANT_DB_NAME="${TENANT_DB_NAME:-}"
TENANT_DB_USER="${TENANT_DB_USER:-postgres}"
TENANT_DB_PASSWORD="${TENANT_DB_PASSWORD:-}"

mkdir -p "$BACKUP_DIR"

if [[ -z "$DB_PASSWORD" ]]; then
    echo "[FAIL] DB_CENTRAL_PASSWORD ou PGPASSWORD deve estar configurado para backup." >&2
    exit 1
fi

if ! command -v pg_dump >/dev/null 2>&1; then
    echo "[FAIL] pg_dump nao encontrado." >&2
    exit 1
fi

export PGPASSWORD="$DB_PASSWORD"
pg_dump \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --username="$DB_USER" \
    --format=custom \
    --file="$BACKUP_DIR/central_${DB_NAME}_${TIMESTAMP}.dump" \
    "$DB_NAME"

echo "[PASS] Backup central gerado em $BACKUP_DIR/central_${DB_NAME}_${TIMESTAMP}.dump"

if [[ -n "$TENANT_DB_HOST" && -n "$TENANT_DB_NAME" ]]; then
    if [[ -z "$TENANT_DB_PASSWORD" ]]; then
        echo "[FAIL] TENANT_DB_PASSWORD deve estar configurado para backup de tenant." >&2
        exit 1
    fi

    export PGPASSWORD="$TENANT_DB_PASSWORD"
    pg_dump \
        --host="$TENANT_DB_HOST" \
        --port="$TENANT_DB_PORT" \
        --username="$TENANT_DB_USER" \
        --format=custom \
        --file="$BACKUP_DIR/tenant_${TENANT_DB_NAME}_${TIMESTAMP}.dump" \
        "$TENANT_DB_NAME"

    echo "[PASS] Backup tenant gerado em $BACKUP_DIR/tenant_${TENANT_DB_NAME}_${TIMESTAMP}.dump"
fi
