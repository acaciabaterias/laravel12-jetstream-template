#!/usr/bin/env bash

set -euo pipefail

ERP_URL="${ERP_URL:-http://localhost:8000}"
MS001_URL="${MS001_URL:-http://localhost:8001/v1/health}"
MS002_URL="${MS002_URL:-http://localhost:8002/v1/health}"
MS003_URL="${MS003_URL:-http://localhost:8003/v1/health}"
MS004_URL="${MS004_URL:-http://localhost:8004/v1/health}"
MS005_URL="${MS005_URL:-http://localhost:8005/v1/health}"

check_url() {
    local name="$1"
    local url="$2"

    if command -v curl >/dev/null 2>&1 && curl --silent --fail --max-time 5 "$url" >/dev/null; then
        echo "[PASS] $name => $url"
    else
        echo "[FAIL] $name => $url"
        return 1
    fi
}

status=0

check_url "ERP Core" "$ERP_URL/up" || status=1
check_url "MS-001 Fiscal" "$MS001_URL" || status=1
check_url "MS-002 Bancario" "$MS002_URL" || status=1
check_url "MS-003 WhatsApp" "$MS003_URL" || status=1
check_url "MS-004 Open Finance" "$MS004_URL" || status=1
check_url "MS-005 Geocoding" "$MS005_URL" || status=1

exit "$status"
