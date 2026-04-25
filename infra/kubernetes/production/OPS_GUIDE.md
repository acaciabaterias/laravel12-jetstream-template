# OPS Guide

## Objetivo

Este guia resume a rotina operacional para o time de suporte apos o deploy em Kubernetes.

## Recursos principais

- Namespace de producao: `bateriaexpert`
- ERP Core:
  - `deployment/erp-core-web`
  - `deployment/erp-core-queue`
  - `deployment/erp-core-scheduler`
- Microservicos:
  - `deployment/ms-001-fiscal-api`
  - `deployment/ms-002-banking-api`
  - `deployment/ms-003-notification-api`
  - `deployment/ms-004-openfinance-api`
  - `deployment/ms-005-geocoding-api`
- Banco central:
  - `statefulset/postgres-central`
- Cache e filas:
  - `deployment/redis-master`

## Diagnostico rapido

1. Verificar pods:
   `kubectl get pods -n bateriaexpert`
2. Verificar rollout:
   `./infra/kubernetes/production/verify-k8s.sh`
3. Verificar ingress e services:
   `kubectl get svc,ingress -n bateriaexpert`
4. Rodar smoke test externo:
   `./infra/kubernetes/production/smoke-test.sh`

## Health endpoints

- ERP Core: `GET /up`
- MS-001: `GET /api/v1/health`
- MS-002: `GET /api/v1/health`
- MS-003: `GET /api/v1/health`
- MS-004: `GET /api/v1/health`
- MS-005: `GET /api/v1/health`

## Logs

### ERP Core

- Web:
  `kubectl logs deployment/erp-core-web -n bateriaexpert --tail=200`
- Queue:
  `kubectl logs deployment/erp-core-queue -n bateriaexpert --tail=200`
- Scheduler:
  `kubectl logs deployment/erp-core-scheduler -n bateriaexpert --tail=200`

### Microservicos

- `kubectl logs deployment/ms-001-fiscal-api -n bateriaexpert --tail=200`
- `kubectl logs deployment/ms-002-banking-api -n bateriaexpert --tail=200`
- `kubectl logs deployment/ms-003-notification-api -n bateriaexpert --tail=200`
- `kubectl logs deployment/ms-004-openfinance-api -n bateriaexpert --tail=200`
- `kubectl logs deployment/ms-005-geocoding-api -n bateriaexpert --tail=200`

### Banco e Redis

- `kubectl logs statefulset/postgres-central -n bateriaexpert --tail=200`
- `kubectl logs deployment/redis-master -n bateriaexpert --tail=200`

## Restart controlado

Use restart apenas depois de validar logs, readiness e dependencia externa.

- ERP web:
  `kubectl rollout restart deployment/erp-core-web -n bateriaexpert`
- Queue:
  `kubectl rollout restart deployment/erp-core-queue -n bateriaexpert`
- Scheduler:
  `kubectl rollout restart deployment/erp-core-scheduler -n bateriaexpert`
- Microservico especifico:
  `kubectl rollout restart deployment/ms-001-fiscal-api -n bateriaexpert`

## Casos comuns

### ERP indisponivel

1. Verificar `ingress`, `service` e pods de `erp-core-web`.
2. Validar `kubectl describe pod` em um pod com falha.
3. Conferir conectividade com PostgreSQL e Redis.

### Fila parada

1. Conferir `deployment/erp-core-queue`.
2. Validar logs do worker.
3. Conferir `redis-master`.

### Scheduler nao executa tarefas

1. Conferir `deployment/erp-core-scheduler`.
2. Validar logs e reiniciar rollout se necessario.

### Microservico com falha

1. Verificar `kubectl get pods -n bateriaexpert`.
2. Inspecionar logs do deployment afetado.
3. Conferir secret, configmap e dependencias do servico.

## Comandos uteis

- Descrever recurso:
  `kubectl describe deployment erp-core-web -n bateriaexpert`
- Entrar em pod:
  `kubectl exec -it deploy/erp-core-web -n bateriaexpert -- sh`
- Ver eventos recentes:
  `kubectl get events -n bateriaexpert --sort-by=.metadata.creationTimestamp`

## Escalacao

Escalar para engenharia quando houver:

- falha recorrente de readiness/liveness
- erro de migrations ou conexao com banco
- falha de certificados/ingress
- perda de conectividade entre ERP e microservicos
- backlog crescente de filas sem recuperacao apos restart
