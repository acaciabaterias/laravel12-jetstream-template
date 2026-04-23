#!/usr/bin/env bash

set -euo pipefail

if [[ $# -lt 1 ]]; then
    echo "Uso: ./restore.sh <arquivo.dump> [database]" >&2
    exit 1
fi

DUMP_FILE="$1"
TARGET_DB="${2:-${DB_CENTRAL_DATABASE:-erp_central}}"
DB_HOST="${DB_CENTRAL_HOST:-localhost}"
DB_PORT="${DB_CENTRAL_PORT:-5432}"
DB_USER="${DB_CENTRAL_USERNAME:-gil}"
DB_PASSWORD="${DB_CENTRAL_PASSWORD:-${PGPASSWORD:-}}"

if ! command -v pg_restore >/dev/null 2>&1; then
    echo "[FAIL] pg_restore nao encontrado." >&2
    exit 1
fi

if [[ ! -f "$DUMP_FILE" ]]; then
    echo "[FAIL] Arquivo de dump nao encontrado: $DUMP_FILE" >&2
    exit 1
fi

export PGPASSWORD="$DB_PASSWORD"

pg_restore \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --username="$DB_USER" \
    --dbname="$TARGET_DB" \
    --clean \
    --if-exists \
    --no-owner \
    --no-privileges \
    "$DUMP_FILE"

echo "[PASS] Restore concluido em $TARGET_DB a partir de $DUMP_FILE"
