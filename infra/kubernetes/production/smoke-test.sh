#!/usr/bin/env sh

set -eu

ERP_URL="${ERP_URL:-https://erp.example.com}"
MS001_URL="${MS001_URL:-https://ms-001.example.com/api/v1/health}"
MS002_URL="${MS002_URL:-https://ms-002.example.com/api/v1/health}"
MS003_URL="${MS003_URL:-https://ms-003.example.com/api/v1/health}"
MS004_URL="${MS004_URL:-https://ms-004.example.com/api/v1/health}"
MS005_URL="${MS005_URL:-https://ms-005.example.com/api/v1/health}"
CURL_BIN="${CURL_BIN:-curl}"

require_command() {
    if ! command -v "$1" >/dev/null 2>&1; then
        echo "[smoke-test] comando obrigatorio ausente: $1" >&2
        exit 1
    fi
}

check_url() {
    name="$1"
    url="$2"

    echo "[smoke-test] verificando ${name}: ${url}"

    if "$CURL_BIN" --silent --show-error --fail --location --max-time 10 "$url" >/dev/null; then
        echo "[smoke-test] OK - ${name}"
    else
        echo "[smoke-test] FALHA - ${name}" >&2
        return 1
    fi
}

require_command "$CURL_BIN"

status=0

check_url "ERP Core" "${ERP_URL}/up" || status=1
check_url "MS-001 Fiscal ACBr" "${MS001_URL}" || status=1
check_url "MS-002 Bancario" "${MS002_URL}" || status=1
check_url "MS-003 WhatsApp n8n" "${MS003_URL}" || status=1
check_url "MS-004 Open Finance" "${MS004_URL}" || status=1
check_url "MS-005 Geocoding" "${MS005_URL}" || status=1

if [ "$status" -eq 0 ]; then
    echo "[smoke-test] todos os endpoints responderam com sucesso."
else
    echo "[smoke-test] foram detectadas falhas no smoke test." >&2
fi

exit "$status"
