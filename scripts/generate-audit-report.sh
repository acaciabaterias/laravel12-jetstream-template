#!/bin/bash

# Script: generate-audit-report.sh
# Gera relatório de auditoria completo

set -e

OUTPUT_DIR="audit-reports"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
OUTPUT_FILE="${OUTPUT_DIR}/relatorio_auditoria_${TIMESTAMP}.md"

mkdir -p $OUTPUT_DIR

echo "📊 Gerando relatório de auditoria..."

# Usar comandos artisan para coletar dados
php artisan audit:generate-report --output=$OUTPUT_FILE --format=markdown

# Exportar CSVs para anexos
php artisan audit:export --days=90 --format=csv --output="${OUTPUT_DIR}/audit_full_${TIMESTAMP}.csv"
php artisan audit:export --days=90 --format=json --output="${OUTPUT_DIR}/audit_summary_${TIMESTAMP}.json"

echo "✅ Relatório gerado: $OUTPUT_FILE"
echo "📎 Anexos:"
echo "   - CSV: ${OUTPUT_DIR}/audit_full_${TIMESTAMP}.csv"
echo "   - JSON: ${OUTPUT_DIR}/audit_summary_${TIMESTAMP}.json"
