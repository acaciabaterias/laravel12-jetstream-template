#!/usr/bin/env bash
# =============================================================================
# run-benchmark.sh — Orquestra o ciclo completo de benchmark do ERP
# Uso: ./scripts/run-benchmark.sh [--skip-docker] [--k6-scenario <nome>]
# =============================================================================
set -euo pipefail

# ── Cores ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'; YELLOW='\033[1;33m'; GREEN='\033[0;32m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'

# ── Config ─────────────────────────────────────────────────────────────────
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
K6_LOG="${PROJECT_DIR}/test-reports/k6_${TIMESTAMP}.log"
SKIP_DOCKER=false
K6_SCENARIO=""
HEALTH_WAIT=30
PROM_URL="${PROM_URL:-http://192.168.1.130:9090}"

mkdir -p "${PROJECT_DIR}/test-reports"

# ── Parse args ─────────────────────────────────────────────────────────────
while [[ $# -gt 0 ]]; do
  case "$1" in
    --skip-docker) SKIP_DOCKER=true; shift ;;
    --k6-scenario) K6_SCENARIO="$2"; shift 2 ;;
    --wait) HEALTH_WAIT="$2"; shift 2 ;;
    --prom) PROM_URL="$2"; shift 2 ;;
    *) echo -e "${RED}Argumento desconhecido: $1${NC}"; exit 1 ;;
  esac
done

# ── Banner ─────────────────────────────────────────────────────────────────
echo -e "\n${CYAN}${BOLD}"
echo "  ██████╗  █████╗ ████████╗███████╗██████╗ ██╗ █████╗ "
echo "  ██╔══██╗██╔══██╗╚══██╔══╝██╔════╝██╔══██╗██║██╔══██╗"
echo "  ██████╔╝███████║   ██║   █████╗  ██████╔╝██║███████║"
echo "  ██╔══██╗██╔══██║   ██║   ██╔══╝  ██╔══██╗██║██╔══██║"
echo "  ██████╔╝██║  ██║   ██║   ███████╗██║  ██║██║██║  ██║"
echo "  ╚═════╝ ╚═╝  ╚═╝   ╚═╝   ╚══════╝╚═╝  ╚═╝╚═╝╚═╝  ╚═╝"
echo -e "${NC}"
echo -e "${BOLD}  ERP Multi-Tenant — Benchmark Orquestrador${NC}"
echo -e "  ${BLUE}Timestamp:${NC} ${TIMESTAMP}"
echo -e "  ${BLUE}Prometheus:${NC} ${PROM_URL}"
echo -e "  ${BLUE}Relatório K6:${NC} ${K6_LOG}\n"
echo -e "${CYAN}${BOLD}════════════════════════════════════════════${NC}\n"

# ── Passo 1: Docker Stack ─────────────────────────────────────────────────
step() { echo -e "${CYAN}${BOLD}[STEP $1]${NC} $2"; }
ok()   { echo -e "${GREEN}  ✓ $1${NC}"; }
warn() { echo -e "${YELLOW}  ⚠ $1${NC}"; }
fail() { echo -e "${RED}  ✗ $1${NC}"; }

step "1" "Infraestrutura Docker"

if [[ "$SKIP_DOCKER" == "true" ]]; then
  warn "Pulando inicialização Docker (--skip-docker)"
else
  echo -e "  Subindo: erp_core_app, erp_core_db, erp_core_redis, prometheus..."
  cd "$PROJECT_DIR"
  docker compose up -d erp_core_app erp_core_db erp_core_redis prometheus 2>&1 \
    | grep -E "(Started|Running|Healthy|Error)" | sed 's/^/  /' || true
  ok "Containers iniciados"
fi

# ── Passo 2: Healthcheck ───────────────────────────────────────────────────
step "2" "Aguardando healthcheck do Laravel (timeout: ${HEALTH_WAIT}s)"

APP_HOST="${APP_HOST:-http://erp_core_app:80}"
# Para testes locais sem docker, tenta localhost
LOCAL_CHECK="http://192.168.1.130:${ERP_PORT:-8500}"

waited=0
healthy=false
check_url="$LOCAL_CHECK/up"

while [[ $waited -lt $HEALTH_WAIT ]]; do
  if curl -sf --max-time 3 "$check_url" > /dev/null 2>&1; then
    healthy=true; break
  fi
  printf "  . "
  sleep 2; waited=$((waited + 2))
done
echo ""

if [[ "$healthy" == "true" ]]; then
  ok "Laravel respondendo em ${check_url}"
else
  warn "Laravel não respondeu via ${check_url} em ${HEALTH_WAIT}s"
  warn "O k6 tentará via rede interna do Docker (erp_core_app:80)"
fi

# ── Passo 3: Executar K6 ───────────────────────────────────────────────────
step "3" "Executando testes de carga K6"
echo -e "  Script: ${PROJECT_DIR}/k6/load-test.js"
echo -e "  Log:    ${K6_LOG}\n"

K6_EXTRA_ARGS=""
if [[ -n "$K6_SCENARIO" ]]; then
  K6_EXTRA_ARGS="--scenario $K6_SCENARIO"
  echo -e "  ${BLUE}Cenário filtrado:${NC} ${K6_SCENARIO}"
fi

cd "$PROJECT_DIR"

set +e
docker compose run --rm \
  -e INTERNAL_KEY="${INTERNAL_SERVICE_KEY:-your-very-long-secret-key-here-1234567890abcdef}" \
  k6 run $K6_EXTRA_ARGS /scripts/load-test.js 2>&1 | tee "$K6_LOG"
K6_EXIT=${PIPESTATUS[0]}
set -e

echo ""
if [[ $K6_EXIT -eq 0 ]]; then
  ok "K6 finalizado com sucesso (exit 0)"
elif [[ $K6_EXIT -eq 99 ]]; then
  warn "K6 finalizado com thresholds violados (exit 99) — normal para baseline"
else
  fail "K6 encerrou com erro (exit ${K6_EXIT})"
fi

# ── Passo 4: Extrair métricas K6 do log ───────────────────────────────────
step "4" "Extraindo métricas do log K6"

extract_k6() {
  local label="$1" pattern="$2"
  grep -oP "${pattern}" "$K6_LOG" 2>/dev/null | tail -1 || echo "N/A"
}

K6_P95=$(extract_k6 "p95" '(?<=p\(95\)=)[0-9.]+m?s')
K6_P50=$(extract_k6 "p50" '(?<=med=)[0-9.]+m?s')
K6_REQS=$(extract_k6 "reqs" '(?<=http_reqs\.+: )[0-9]+')
K6_FAILS=$(extract_k6 "fails" '(?<=http_req_failed\.+: )[0-9.]+%')
K6_DROPPED=$(extract_k6 "dropped" '(?<=dropped_iterations\.+: )[0-9]+')

echo -e "  ${BOLD}Resumo K6 (do log):${NC}"
echo -e "    Total requisições : ${K6_REQS}"
echo -e "    Falhas            : ${K6_FAILS}"
echo -e "    P50               : ${K6_P50}"
echo -e "    P95               : ${K6_P95}"
echo -e "    Dropped iters     : ${K6_DROPPED}"

# ── Passo 5: Relatório Prometheus ─────────────────────────────────────────
step "5" "Gerando relatório Prometheus"

if curl -sf --max-time 5 "${PROM_URL}/-/healthy" > /dev/null 2>&1; then
  bash "${SCRIPT_DIR}/post-test-report.sh" "$PROM_URL"
else
  warn "Prometheus offline — pulando coleta de métricas."
  warn "Inicie com: docker compose up -d prometheus"

  # Gera relatório apenas com dados do k6
  REPORT_FILE="${PROJECT_DIR}/test-reports/benchmark_${TIMESTAMP}.md"
  cat > "$REPORT_FILE" <<MDEOF
# 📊 BateriaExpert ERP — Benchmark Report (K6 Only)

**Gerado em:** $(date '+%Y-%m-%d %H:%M:%S')
> ⚠️ Prometheus offline — apenas métricas K6 disponíveis.

## K6 Results

| Métrica | Valor |
|---|---|
| Total de Requisições | ${K6_REQS} |
| Falhas | ${K6_FAILS} |
| Latência P50 | ${K6_P50} |
| Latência P95 | ${K6_P95} |
| Dropped Iterations | ${K6_DROPPED} |

**Log completo:** \`${K6_LOG}\`
MDEOF
  ok "Relatório K6 gerado: ${REPORT_FILE}"
fi

# ── Passo 6: Resumo Final ──────────────────────────────────────────────────
echo -e "\n${CYAN}${BOLD}════════════════════════════════════════════${NC}"
echo -e "${GREEN}${BOLD}  ✅ Benchmark Concluído!${NC}"
echo -e "${CYAN}${BOLD}════════════════════════════════════════════${NC}\n"
echo -e "  ${BOLD}Artefatos gerados:${NC}"
echo -e "  • Log K6    : ${K6_LOG}"
ls "${PROJECT_DIR}/test-reports/benchmark_${TIMESTAMP}"*.md 2>/dev/null \
  | while read -r f; do echo -e "  • Relatório : ${f}"; done
echo ""
echo -e "  ${BLUE}Dicas:${NC}"
echo -e "  • Prometheus : http://192.168.1.130:9090"
echo -e "  • Grafana    : http://192.168.1.130:3000 (admin/admin)"
echo -e "  • Portainer  : http://192.168.1.130:9000\n"
