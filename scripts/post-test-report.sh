#!/usr/bin/env bash
# =============================================================================
# post-test-report.sh — Coleta métricas do Prometheus e gera relatório Markdown
# Uso: ./scripts/post-test-report.sh [prometheus_url]
# =============================================================================
set -euo pipefail

# ── Cores ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'; YELLOW='\033[1;33m'; GREEN='\033[0;32m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'

# ── Config ─────────────────────────────────────────────────────────────────
PROM="${1:-http://localhost:9090}"
GRAFANA_URL="${GRAFANA_URL:-http://192.168.1.130:3000}"
REPORT_DIR="$(cd "$(dirname "$0")/.." && pwd)/test-reports"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
REPORT_FILE="${REPORT_DIR}/benchmark_${TIMESTAMP}.md"
RANGE="5m"

mkdir -p "$REPORT_DIR"

# ── Helpers ────────────────────────────────────────────────────────────────
prom_query() {
  local query="$1"
  local result
  result=$(curl -sf --max-time 5 \
    "${PROM}/api/v1/query" \
    --data-urlencode "query=${query}" 2>/dev/null) || { echo "N/A"; return; }
  echo "$result" | jq -r '.data.result[0].value[1] // "N/A"' 2>/dev/null || echo "N/A"
}

prom_query_range() {
  local query="$1"
  local result
  result=$(curl -sf --max-time 5 \
    "${PROM}/api/v1/query" \
    --data-urlencode "query=${query}" 2>/dev/null) || { echo "N/A"; return; }
  echo "$result" | jq -r '[.data.result[].value[1]] | map(tonumber) | add / length // "N/A"' 2>/dev/null || echo "N/A"
}

fmt_num() {
  local v="$1"
  [[ "$v" == "N/A" || -z "$v" ]] && echo "N/A" && return
  printf "%.4f" "$v" 2>/dev/null || echo "$v"
}

fmt_ms() {
  local v="$1"
  [[ "$v" == "N/A" || -z "$v" ]] && echo "N/A" && return
  printf "%.1f ms" "$(echo "$v * 1000" | bc -l 2>/dev/null || echo 0)"
}

cmp_float() {
  # cmp_float val op threshold → 0 se verdadeiro
  local val="$1" op="$2" thr="$3"
  [[ "$val" == "N/A" || -z "$val" ]] && return 1
  echo "$val $op $thr" | bc -l 2>/dev/null | grep -q "^1$"
}

# ── Verificar Prometheus ───────────────────────────────────────────────────
echo -e "\n${CYAN}${BOLD}═══════════════════════════════════════════════════${NC}"
echo -e "${CYAN}${BOLD}  BateriaExpert ERP — Relatório de Benchmark${NC}"
echo -e "${CYAN}${BOLD}═══════════════════════════════════════════════════${NC}\n"

if ! curl -sf --max-time 5 "${PROM}/-/healthy" > /dev/null 2>&1; then
  echo -e "${RED}✗ Prometheus offline em ${PROM}${NC}"
  echo -e "${YELLOW}  Dica: docker compose up -d prometheus${NC}\n"
  # Gera relatório mínimo com aviso
  cat > "$REPORT_FILE" <<MDEOF
# Benchmark Report — ${TIMESTAMP}

> **⚠️ Prometheus offline** — métricas não disponíveis em \`${PROM}\`.
> Execute \`docker compose up -d prometheus\` e tente novamente.
MDEOF
  echo -e "${YELLOW}  Relatório parcial gerado: ${REPORT_FILE}${NC}\n"
  exit 1
fi

echo -e "${GREEN}✓ Prometheus online${NC} — coletando métricas (janela: ${RANGE})...\n"

# ── Coleta de Métricas ─────────────────────────────────────────────────────
echo -e "${BLUE}→ HTTP Requests & Latência...${NC}"
HTTP_RPS=$(prom_query "sum(rate(app_http_requests_total[${RANGE}]))")
HTTP_RPS_FMT=$(fmt_num "$HTTP_RPS")

HTTP_P95_RAW=$(prom_query "histogram_quantile(0.95, sum(rate(app_http_request_duration_ms_bucket[${RANGE}])) by (le))")
HTTP_P95_MS=$(fmt_ms "$HTTP_P95_RAW")

HTTP_P50_RAW=$(prom_query "histogram_quantile(0.50, sum(rate(app_http_request_duration_ms_bucket[${RANGE}])) by (le))")
HTTP_P50_MS=$(fmt_ms "$HTTP_P50_RAW")

HTTP_P99_RAW=$(prom_query "histogram_quantile(0.99, sum(rate(app_http_request_duration_ms_bucket[${RANGE}])) by (le))")
HTTP_P99_MS=$(fmt_ms "$HTTP_P99_RAW")

echo -e "${BLUE}→ Taxa de Erros (4xx/5xx)...${NC}"
HTTP_ERRORS=$(prom_query "sum(rate(app_http_requests_total{status=~\"4..|5..\"}[${RANGE}]))")
HTTP_TOTAL=$(prom_query "sum(rate(app_http_requests_total[${RANGE}]))")

ERROR_RATE="N/A"
if [[ "$HTTP_ERRORS" != "N/A" && "$HTTP_TOTAL" != "N/A" && "$HTTP_TOTAL" != "0" ]]; then
  ERROR_RATE=$(echo "scale=4; $HTTP_ERRORS / $HTTP_TOTAL * 100" | bc -l 2>/dev/null || echo "N/A")
fi
ERROR_RATE_FMT=$(fmt_num "$ERROR_RATE")

echo -e "${BLUE}→ Autenticação Interna...${NC}"
AUTH_FAILURES=$(prom_query "sum(increase(app_internal_auth_failures_total[${RANGE}]))")
AUTH_FAILURES_FMT=$(fmt_num "$AUTH_FAILURES")

AUTH_FAIL_INVALID=$(prom_query "sum(increase(app_internal_auth_failures_total{reason=\"invalid_key\"}[${RANGE}]))")
AUTH_FAIL_MISSING=$(prom_query "sum(increase(app_internal_auth_failures_total{reason=\"missing_client_key\"}[${RANGE}]))")
INTERNAL_TOTAL=$(prom_query "sum(increase(app_internal_requests_total[${RANGE}]))")

echo -e "${BLUE}→ Métricas de Tenant...${NC}"
TENANTS_OK=$(prom_query "sum(increase(app_tenants_created_total{status=\"success\"}[${RANGE}]))")
TENANTS_FAIL=$(prom_query "sum(increase(app_tenants_created_total{status=\"failed\"}[${RANGE}]))")
TENANT_DUR_RAW=$(prom_query "histogram_quantile(0.95, sum(rate(app_tenant_creation_duration_seconds_bucket[${RANGE}])) by (le))")
TENANT_DUR_FMT=$(fmt_ms "$TENANT_DUR_RAW")

echo -e "${BLUE}→ Recursos do Container Laravel...${NC}"
CPU_USAGE=$(prom_query "rate(container_cpu_usage_seconds_total{name=\"erp_core_app\"}[${RANGE}]) * 100")
MEM_USAGE=$(prom_query "container_memory_usage_bytes{name=\"erp_core_app\"}")
MEM_MB="N/A"
if [[ "$MEM_USAGE" != "N/A" && -n "$MEM_USAGE" ]]; then
  MEM_MB=$(echo "scale=1; $MEM_USAGE / 1024 / 1024" | bc -l 2>/dev/null || echo "N/A")
fi

echo ""

# ── Análise Inteligente ────────────────────────────────────────────────────
ALERTS=()
PRAISES=()
RECS=()

# P95 latência
if cmp_float "$HTTP_P95_RAW" ">" "0.500"; then
  ALERTS+=("🔴 **Latência crítica**: P95 = ${HTTP_P95_MS} (threshold: 500ms)")
  RECS+=("- Habilitar OPcache no PHP e revisar queries N+1")
  RECS+=("- Considerar Redis para cache de responses frequentes")
elif cmp_float "$HTTP_P95_RAW" ">" "0.200"; then
  ALERTS+=("🟡 **Latência elevada**: P95 = ${HTTP_P95_MS} (threshold: 200ms)")
  RECS+=("- Revisar middleware stack e adicionar cache de rotas: \`php artisan route:cache\`")
elif cmp_float "$HTTP_P95_RAW" "<" "0.200" && [[ "$HTTP_P95_RAW" != "N/A" ]]; then
  PRAISES+=("✅ **Excelente latência**: P95 = ${HTTP_P95_MS} — abaixo do threshold de 200ms")
fi

# Taxa de erro
if cmp_float "$ERROR_RATE" ">" "1"; then
  ALERTS+=("🔴 **Taxa de erro alta**: ${ERROR_RATE_FMT}% (threshold: 1%)")
  RECS+=("- Verificar logs do Laravel: \`docker compose logs erp_core_app --tail=100\`")
  RECS+=("- Revisar migrations pendentes e configurações de DB")
elif cmp_float "$ERROR_RATE" ">" "0.1" && [[ "$ERROR_RATE" != "N/A" ]]; then
  ALERTS+=("🟡 **Taxa de erro moderada**: ${ERROR_RATE_FMT}%")
elif [[ "$ERROR_RATE" != "N/A" ]]; then
  PRAISES+=("✅ **Taxa de erro saudável**: ${ERROR_RATE_FMT}% — dentro do normal")
fi

# Falhas de autenticação
if cmp_float "$AUTH_FAILURES" ">" "10"; then
  ALERTS+=("🔴 **Alerta de segurança**: ${AUTH_FAILURES_FMT} falhas de autenticação interna!")
  RECS+=("- Verificar logs de acesso: possível tentativa de acesso indevido aos webhooks")
  RECS+=("- Rotacionar a \`INTERNAL_SERVICE_KEY\` imediatamente")
elif cmp_float "$AUTH_FAILURES" ">" "0" && [[ "$AUTH_FAILURES" != "N/A" ]]; then
  ALERTS+=("🟡 **Atenção**: ${AUTH_FAILURES_FMT} falhas de autenticação detectadas")
fi

# CPU
if cmp_float "$CPU_USAGE" ">" "80"; then
  ALERTS+=("🔴 **CPU alta**: ${CPU_USAGE}% no container erp_core_app")
  RECS+=("- Aumentar workers PHP-FPM ou escalar horizontalmente")
elif cmp_float "$CPU_USAGE" ">" "60" && [[ "$CPU_USAGE" != "N/A" ]]; then
  ALERTS+=("🟡 **CPU moderada**: ${CPU_USAGE}% — monitorar tendência")
fi

# ── Output Terminal ────────────────────────────────────────────────────────
echo -e "${BOLD}📊 RESUMO DE MÉTRICAS${NC}"
echo -e "─────────────────────────────────────────"
echo -e "  Throughput    : ${BOLD}${HTTP_RPS_FMT} req/s${NC}"
echo -e "  Latência P50  : ${HTTP_P50_MS}"
echo -e "  Latência P95  : ${BOLD}${HTTP_P95_MS}${NC}"
echo -e "  Latência P99  : ${HTTP_P99_MS}"
echo -e "  Taxa de Erro  : ${BOLD}${ERROR_RATE_FMT}%${NC}"
echo -e "  Auth Failures : ${AUTH_FAILURES_FMT}"
echo -e "  Tenants OK    : ${TENANTS_OK}  |  Fail: ${TENANTS_FAIL}"
echo -e "  CPU Container : ${CPU_USAGE}%"
echo -e "  RAM Container : ${MEM_MB} MB"
echo -e "─────────────────────────────────────────\n"

if [[ ${#PRAISES[@]} -gt 0 ]]; then
  echo -e "${GREEN}${BOLD}✅ DESTAQUES POSITIVOS${NC}"
  for p in "${PRAISES[@]}"; do echo -e "  ${GREEN}${p}${NC}"; done
  echo ""
fi

if [[ ${#ALERTS[@]} -gt 0 ]]; then
  echo -e "${RED}${BOLD}⚠️  ALERTAS${NC}"
  for a in "${ALERTS[@]}"; do echo -e "  ${YELLOW}${a}${NC}"; done
  echo ""
fi

if [[ ${#RECS[@]} -gt 0 ]]; then
  echo -e "${CYAN}${BOLD}💡 RECOMENDAÇÕES${NC}"
  for r in "${RECS[@]}"; do echo -e "  ${r}"; done
  echo ""
fi

# ── Gerar Relatório Markdown ───────────────────────────────────────────────
ALERT_BLOCK=""
for a in "${ALERTS[@]}"; do ALERT_BLOCK+="- ${a}\n"; done

PRAISE_BLOCK=""
for p in "${PRAISES[@]}"; do PRAISE_BLOCK+="- ${p}\n"; done

REC_BLOCK=""
for r in "${RECS[@]}"; do REC_BLOCK+="${r}\n"; done

cat > "$REPORT_FILE" <<MDEOF
# 📊 BateriaExpert ERP — Benchmark Report

**Gerado em:** $(date '+%Y-%m-%d %H:%M:%S')
**Prometheus:** \`${PROM}\`
**Janela de análise:** últimos \`${RANGE}\`

---

## 🚀 Métricas de HTTP

| Métrica | Valor |
|---|---|
| Throughput | **${HTTP_RPS_FMT} req/s** |
| Latência P50 | ${HTTP_P50_MS} |
| Latência P95 | **${HTTP_P95_MS}** |
| Latência P99 | ${HTTP_P99_MS} |
| Taxa de Erro (4xx/5xx) | **${ERROR_RATE_FMT}%** |

---

## 🔒 Autenticação Interna

| Métrica | Valor |
|---|---|
| Total de Requisições Internas | ${INTERNAL_TOTAL} |
| Falhas (chave inválida) | ${AUTH_FAIL_INVALID} |
| Falhas (chave ausente) | ${AUTH_FAIL_MISSING} |
| **Total de Falhas** | **${AUTH_FAILURES_FMT}** |

---

## 🏢 Provisionamento de Tenants

| Métrica | Valor |
|---|---|
| Criações com sucesso | ${TENANTS_OK} |
| Criações com falha | ${TENANTS_FAIL} |
| Duração P95 (provisionamento) | ${TENANT_DUR_FMT} |

---

## 🖥️ Recursos do Container \`erp_core_app\`

| Métrica | Valor |
|---|---|
| CPU Usage | ${CPU_USAGE}% |
| Memória | ${MEM_MB} MB |

---

## 🧠 Análise Inteligente

### ✅ Destaques Positivos
$(echo -e "$PRAISE_BLOCK" | sed 's/^$/_(nenhum)_/')

### ⚠️ Alertas
$(echo -e "$ALERT_BLOCK" | sed 's/^$/_(nenhum)_/')

### 💡 Recomendações
$(echo -e "$REC_BLOCK" | sed 's/^$/_(nenhuma)_/')

---

## 📈 Queries Grafana Úteis

\`\`\`promql
# Throughput por rota
sum(rate(app_http_requests_total[5m])) by (path)

# P95 de latência por rota
histogram_quantile(0.95, sum(rate(app_http_request_duration_ms_bucket[5m])) by (le, path))

# Taxa de erros
sum(rate(app_http_requests_total{status=~"4..|5.."}[5m])) / sum(rate(app_http_requests_total[5m])) * 100
\`\`\`

---

*Relatório gerado automaticamente por \`scripts/post-test-report.sh\`*
MDEOF

echo -e "${GREEN}${BOLD}✓ Relatório gerado:${NC} ${REPORT_FILE}\n"

# ── Abrir Grafana? ─────────────────────────────────────────────────────────
read -r -p "$(echo -e "${CYAN}Abrir Grafana no navegador? (${GRAFANA_URL}) [s/N]: ${NC}")" open_grafana
if [[ "${open_grafana,,}" == "s" || "${open_grafana,,}" == "y" ]]; then
  if command -v xdg-open &>/dev/null; then
    xdg-open "$GRAFANA_URL" &
  elif command -v open &>/dev/null; then
    open "$GRAFANA_URL" &
  else
    echo -e "${YELLOW}  Acesse manualmente: ${GRAFANA_URL}${NC}"
  fi
fi
