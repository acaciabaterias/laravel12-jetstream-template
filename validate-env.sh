#!/usr/bin/env sh

set -eu

status=0

require_var() {
    variable_name="$1"
    variable_value="$(printenv "$variable_name" 2>/dev/null || true)"

    if [ -z "$variable_value" ]; then
        echo "[FAIL] Variavel obrigatoria ausente: $variable_name"
        status=1
    else
        echo "[PASS] $variable_name"
    fi
}

warn_var() {
    variable_name="$1"
    variable_value="$(printenv "$variable_name" 2>/dev/null || true)"

    if [ -z "$variable_value" ]; then
        echo "[WARN] Variavel recomendada nao configurada: $variable_name"
    else
        echo "[PASS] $variable_name"
    fi
}

require_var APP_NAME
require_var APP_ENV
require_var APP_KEY
require_var APP_URL
require_var DB_CONNECTION
require_var DB_CENTRAL_DRIVER
require_var DB_CENTRAL_HOST
require_var DB_CENTRAL_PORT
require_var DB_CENTRAL_DATABASE
require_var DB_CENTRAL_USERNAME
require_var DB_CENTRAL_PASSWORD
require_var REDIS_HOST
require_var REDIS_PORT
require_var CACHE_STORE
require_var SESSION_DRIVER
require_var QUEUE_CONNECTION

warn_var MS_FISCAL_URL
warn_var MS_BANCARIO_URL
warn_var MS_WHATSAPP_URL
warn_var MS_OPENFINANCE_URL
warn_var MS_GEOCODING_URL
warn_var MAINTENANCE_MODE

if [ "${APP_KEY#base64:}" = "$APP_KEY" ]; then
    echo "[WARN] APP_KEY nao esta no formato base64:, confirme se a chave foi gerada pelo Laravel."
else
    echo "[PASS] APP_KEY format"
fi

if [ "${APP_ENV:-}" = "production" ]; then
    echo "[INFO] Validando guardrails de producao..."

    if [ "${APP_DEBUG:-false}" != "false" ]; then
        echo "[FAIL] APP_DEBUG deve ser false em producao."
        status=1
    else
        echo "[PASS] APP_DEBUG production"
    fi

    case "${APP_URL:-}" in
        https://*)
            echo "[PASS] APP_URL HTTPS"
            ;;
        *)
            echo "[FAIL] APP_URL deve usar HTTPS em producao."
            status=1
            ;;
    esac

    if [ "${APP_KEY#base64:}" = "$APP_KEY" ]; then
        echo "[FAIL] APP_KEY deve estar no formato base64: em producao."
        status=1
    fi

    case "${SUPER_ADMIN_PASSWORD:-}" in
        ""|"12345678"|"password"|"change-me-before-deploy")
            echo "[FAIL] SUPER_ADMIN_PASSWORD deve ser uma senha forte e nao-placeholder em producao."
            status=1
            ;;
        *)
            echo "[PASS] SUPER_ADMIN_PASSWORD production"
            ;;
    esac

    if [ "${SESSION_SECURE_COOKIE:-false}" != "true" ]; then
        echo "[FAIL] SESSION_SECURE_COOKIE deve ser true em producao."
        status=1
    else
        echo "[PASS] SESSION_SECURE_COOKIE production"
    fi

    if [ "${SESSION_ENCRYPT:-false}" != "true" ]; then
        echo "[FAIL] SESSION_ENCRYPT deve ser true em producao."
        status=1
    else
        echo "[PASS] SESSION_ENCRYPT production"
    fi

    if [ "${CORS_ALLOWED_ORIGINS:-}" = "*" ]; then
        echo "[FAIL] CORS_ALLOWED_ORIGINS nao deve ser * em producao."
        status=1
    fi
fi

exit "$status"
