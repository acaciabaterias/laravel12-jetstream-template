#!/usr/bin/env bash

set -u

DB_HOST="${DB_CENTRAL_HOST:-localhost}"
DB_PORT="${DB_CENTRAL_PORT:-5432}"
DB_NAME="${DB_CENTRAL_DATABASE:-erp_central}"
DB_USER="${DB_CENTRAL_USERNAME:-gil}"
DB_PASSWORD="${DB_CENTRAL_PASSWORD:-${PGPASSWORD:-}}"
POSTGRES_DB="${PGDATABASE_FALLBACK:-postgres}"

if [[ -n "${DB_PASSWORD}" ]]; then
    export PGPASSWORD="${DB_PASSWORD}"
fi

pass_count=0
warn_count=0
fail_count=0

pass() {
    printf '[PASS] %s\n' "$1"
    pass_count=$((pass_count + 1))
}

warn() {
    printf '[WARN] %s\n' "$1"
    warn_count=$((warn_count + 1))
}

fail() {
    printf '[FAIL] %s\n' "$1"
    fail_count=$((fail_count + 1))
}

section() {
    printf '\n== %s ==\n' "$1"
}

have_command() {
    command -v "$1" >/dev/null 2>&1
}

run_psql() {
    local database="$1"
    local sql="$2"

    psql \
        --host="$DB_HOST" \
        --port="$DB_PORT" \
        --username="$DB_USER" \
        --dbname="$database" \
        --tuples-only \
        --no-align \
        --quiet \
        --command="$sql" 2>/dev/null
}

check_binary() {
    section 'PostgreSQL instalado'

    if have_command psql; then
        pass "psql encontrado em $(command -v psql)"
    else
        fail 'psql não encontrado. Instale o pacote postgresql-client/postgresql.'
    fi

    if have_command pg_isready; then
        pass "pg_isready encontrado em $(command -v pg_isready)"
    else
        warn 'pg_isready não encontrado. O script seguirá usando psql para as verificações.'
    fi
}

check_service() {
    section 'Serviço PostgreSQL'

    if have_command systemctl; then
        if systemctl is-active --quiet postgresql; then
            pass 'Serviço postgresql está rodando.'
            return
        fi

        local versioned_service
        versioned_service="$(systemctl list-units --type=service --all 'postgresql*.service' 2>/dev/null | awk '/postgresql/ {print $1; exit}')"

        if [[ -n "${versioned_service}" ]] && systemctl is-active --quiet "${versioned_service}"; then
            pass "Serviço ${versioned_service} está rodando."
            return
        fi

        warn 'Nenhum serviço postgresql ativo detectado via systemctl.'
        return
    fi

    if have_command service; then
        if service postgresql status >/dev/null 2>&1; then
            pass 'Serviço postgresql respondeu ao comando service.'
            return
        fi

        warn 'Não foi possível confirmar o serviço via service.'
        return
    fi

    warn 'Nem systemctl nem service estão disponíveis para verificar o daemon.'
}

check_accepting_connections() {
    section 'Aceitando conexões'

    if have_command pg_isready; then
        if pg_isready --host="$DB_HOST" --port="$DB_PORT" >/dev/null 2>&1; then
            pass "PostgreSQL responde em ${DB_HOST}:${DB_PORT}."
            return
        fi

        fail "PostgreSQL não responde em ${DB_HOST}:${DB_PORT}."
        return
    fi

    if run_psql "$POSTGRES_DB" 'select 1;' >/dev/null; then
        pass "Conexão TCP em ${DB_HOST}:${DB_PORT} funcionando."
    else
        fail "Não foi possível abrir conexão em ${DB_HOST}:${DB_PORT}."
    fi
}

check_database_exists() {
    section 'Banco erp_central'

    local exists
    exists="$(run_psql "$POSTGRES_DB" "select 1 from pg_database where datname = '${DB_NAME}';" | tr -d '[:space:]')"

    if [[ "${exists}" == '1' ]]; then
        pass "Banco ${DB_NAME} existe."
    else
        fail "Banco ${DB_NAME} não existe."
    fi
}

check_permissions() {
    section 'Permissões do usuário'

    if ! run_psql "$DB_NAME" 'select current_user;' >/dev/null; then
        fail "Usuário ${DB_USER} não conseguiu conectar no banco ${DB_NAME}."
        return
    fi

    pass "Usuário ${DB_USER} conseguiu conectar no banco ${DB_NAME}."

    local connect_priv
    connect_priv="$(run_psql "$DB_NAME" "select has_database_privilege(current_user, current_database(), 'CONNECT');" | tr -d '[:space:]')"
    if [[ "${connect_priv}" == 't' ]]; then
        pass "Usuário ${DB_USER} tem privilégio CONNECT em ${DB_NAME}."
    else
        fail "Usuário ${DB_USER} não tem privilégio CONNECT em ${DB_NAME}."
    fi

    local schema_priv
    schema_priv="$(run_psql "$DB_NAME" "select has_schema_privilege(current_user, 'public', 'USAGE,CREATE');" | tr -d '[:space:]')"
    if [[ "${schema_priv}" == 't' ]]; then
        pass "Usuário ${DB_USER} tem USAGE/CREATE no schema public."
    else
        fail "Usuário ${DB_USER} não tem USAGE/CREATE no schema public."
    fi

    local create_priv
    create_priv="$(run_psql "$DB_NAME" "select has_database_privilege(current_user, current_database(), 'CREATE');" | tr -d '[:space:]')"
    if [[ "${create_priv}" == 't' ]]; then
        pass "Usuário ${DB_USER} tem privilégio CREATE no banco."
    else
        warn "Usuário ${DB_USER} não tem privilégio CREATE no banco. Isso pode ser aceitável se o schema public já for suficiente."
    fi
}

summary() {
    section 'Resumo'
    printf 'PASS=%s WARN=%s FAIL=%s\n' "$pass_count" "$warn_count" "$fail_count"

    if [[ "$fail_count" -gt 0 ]]; then
        exit 1
    fi
}

check_binary
check_service
check_accepting_connections
check_database_exists
check_permissions
summary
