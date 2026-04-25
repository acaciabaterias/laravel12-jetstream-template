#!/usr/bin/env sh

set -eu

NAMESPACE="${K8S_NAMESPACE:-bateriaexpert}"

require_command() {
    if ! command -v "$1" >/dev/null 2>&1; then
        echo "[verify-k8s] comando obrigatorio ausente: $1" >&2
        exit 1
    fi
}

check_rollout() {
    resource="$1"

    echo "[verify-k8s] aguardando rollout de ${resource}..."
    kubectl rollout status "$resource" -n "$NAMESPACE" --timeout=180s
}

assert_exists() {
    resource="$1"

    echo "[verify-k8s] verificando existencia de ${resource}..."
    kubectl get "$resource" -n "$NAMESPACE" >/dev/null
}

require_command kubectl

echo "[verify-k8s] usando namespace: ${NAMESPACE}"

assert_exists namespace/"$NAMESPACE"

assert_exists statefulset/postgres-central
assert_exists deployment/redis-master
assert_exists deployment/erp-core-web
assert_exists deployment/erp-core-queue
assert_exists deployment/erp-core-scheduler
assert_exists deployment/ms-001-fiscal-api
assert_exists deployment/ms-002-banking-api
assert_exists deployment/ms-003-notification-api
assert_exists deployment/ms-004-openfinance-api
assert_exists deployment/ms-005-geocoding-api

check_rollout statefulset/postgres-central
check_rollout deployment/redis-master
check_rollout deployment/erp-core-web
check_rollout deployment/erp-core-queue
check_rollout deployment/erp-core-scheduler
check_rollout deployment/ms-001-fiscal-api
check_rollout deployment/ms-002-banking-api
check_rollout deployment/ms-003-notification-api
check_rollout deployment/ms-004-openfinance-api
check_rollout deployment/ms-005-geocoding-api

echo "[verify-k8s] resumo de pods"
kubectl get pods -n "$NAMESPACE"

echo "[verify-k8s] resumo de services"
kubectl get svc -n "$NAMESPACE"

echo "[verify-k8s] resumo de ingress"
kubectl get ingress -n "$NAMESPACE"

echo "[verify-k8s] resumo de hpa e pdb"
kubectl get hpa,pdb -n "$NAMESPACE"

echo "[verify-k8s] verificando endpoints de service"
kubectl get endpoints -n "$NAMESPACE"

echo "[verify-k8s] validacao final concluida com sucesso."
