# Quickstart: Módulo 015 - Production Observability Assurance

## Objetivo

Validar localmente a camada de observabilidade operacional e readiness de produção após a estabilização dos módulos `010` a `014`.

## Pré-requisitos

- banco central configurado
- backbone `010` operacional com entregas e replay
- módulos `011` a `014` operacionais no banco central
- Redis disponível quando o cenário exigir backlog e fila
- suíte de testes atual passando como baseline

## Sequência sugerida

1. Aplicar migrations centrais para SLOs, snapshots operacionais, baselines de carga, incidentes e evidências.
2. Reconstruir snapshots operacionais com `php artisan operations:rebuild-health-snapshot`.
3. Validar o dashboard central em `/admin/operations` com filtros de fluxo, severidade, status e incidentes.
4. Validar o endpoint `/admin/operations/inspection` com snapshots, baselines, comparações e evidências.
5. Registrar um baseline de carga por cenário crítico e comparar uma execução degradada.
6. Registrar evidência de runbook, resolver um incidente e encerrar com `post_validation_passed`.
7. Confirmar publicação de eventos operacionais materiais no backbone `010`.

## Cenários de validação

- Identificar degradação por backlog, latência ou falha no backbone.
- Evidenciar incidente financeiro ou de recovery com severidade explícita.
- Comparar baseline de carga atual com cenário previamente aceito.
- Executar replay ou rollback controlado com trilha de evidência.
- Consultar painel executivo operacional e endpoint de inspeção com filtros reaproveitáveis.

## Critérios para avançar à implementação completa

- SLOs e limiares críticos documentados
- baseline de carga reproduzível por fluxo crítico
- evidência mínima exigida para runbooks definida
- distinção clara entre degradação parcial e indisponibilidade real
- eventos operacionais mínimos definidos para o backbone `010`

## Evidência de validação executada

- `php artisan test --compact tests/Feature/ProductionObservabilitySnapshotTest.php`
- `php artisan test --compact tests/Feature/ProductionObservabilityLoadBaselineTest.php tests/Feature/ProductionObservabilityFlowFilterTest.php tests/Unit/ProductionObservabilityBaselineRulesTest.php`
- `php artisan test --compact tests/Feature/ProductionObservabilityIncidentInspectionTest.php tests/Feature/ProductionObservabilityRunbookEvidenceTest.php tests/Unit/ProductionObservabilityIncidentRulesTest.php`
- `php artisan test --compact tests/Feature/ProductionObservabilityDashboardTest.php`
- `php artisan test --compact` com PostgreSQL central/tenant efêmeros: `380 passed`, `1 skipped`, `2096 assertions`
- `vendor/bin/pint --dirty --format agent`
