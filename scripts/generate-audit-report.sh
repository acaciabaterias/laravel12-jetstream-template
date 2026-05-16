#!/usr/bin/env bash

set -e

OUTPUT_DIR="audit-reports"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
OUTPUT_FILE="${OUTPUT_DIR}/relatorio_auditoria_${TIMESTAMP}.md"

mkdir -p "$OUTPUT_DIR"

printf 'Gerando relatorio de auditoria...\n'

php artisan audit:generate-report --output="$OUTPUT_FILE" --format=markdown

php artisan audit:export --days=90 --format=csv --output="${OUTPUT_DIR}/audit_full_${TIMESTAMP}.csv"
php artisan audit:export --days=90 --format=json --output="${OUTPUT_DIR}/audit_summary_${TIMESTAMP}.json"

printf 'Relatorio gerado: %s\n' "$OUTPUT_FILE"
printf 'Anexos:\n'
printf '  - CSV: %s\n' "${OUTPUT_DIR}/audit_full_${TIMESTAMP}.csv"
printf '  - JSON: %s\n' "${OUTPUT_DIR}/audit_summary_${TIMESTAMP}.json"
