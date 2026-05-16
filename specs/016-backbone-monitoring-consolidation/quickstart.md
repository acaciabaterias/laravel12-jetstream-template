# Quickstart: Módulo 016 - Backbone Monitoring Consolidation

## Objetivo

Validar localmente a consolidação do stack externo de monitoramento após a estabilização dos módulos `010` a `015`.

## Pré-requisitos

- banco central configurado
- backbone `010` operacional com métricas e inspeção habilitadas
- módulo `015` concluído e com taxonomia de fluxos/severidade estável
- Prometheus e Grafana disponíveis no ambiente alvo ou simulados para a validação
- exporters e endpoints centrais acessíveis no ambiente de teste

## Sequência sugerida

1. Criar catálogo central de targets, snapshots de probe, regras de alerta, provisão de dashboards e evidências de readiness.
2. Registrar targets mínimos de backbone, billing, payments, recovery, analytics e observability.
3. Validar scrape health e distinguir target degradado de coletor indisponível.
4. Registrar pacote versionado de dashboards e regras de alerta por ambiente.
5. Consultar painel central e endpoint de inspeção de monitoring readiness.
6. Simular rollback de dashboard/alerta e registrar evidência de revalidação.
7. Confirmar publicação de eventos materiais da malha de monitoramento no backbone `010`.

## Cenários de validação

- Detectar exporter offline como degradação explícita do monitoramento.
- Confirmar que dashboards provisionados refletem a mesma taxonomia dos fluxos internos.
- Diferenciar falha do fluxo monitorado e falha do Prometheus/Grafana.
- Registrar readiness de ambiente e rollback de pacote de dashboards.
- Consultar inspeção reutilizável com targets, alertas e evidências.

## Critérios para avançar à implementação completa

- targets mínimos por fluxo crítico documentados
- regras de alerta versionadas com severidade explícita
- provisão de dashboards com ambiente e versão rastreáveis
- readiness mínima disponível mesmo em falha parcial de Grafana
- rollback e revalidação documentados para a malha de monitoramento

## Evidência de validação esperada

- `git diff --check`
- testes direcionados de scrape health, alert rules, provisioning e rollback
- evidência de provisão e revalidação registrada nos artefatos do módulo

## Evidência executada

- `php artisan test --compact tests/Unit/BackboneMonitoringConfigAndPolicyTest.php tests/Unit/BackboneMonitoringAlertRulesTest.php tests/Unit/BackboneMonitoringScrapeRulesTest.php tests/Unit/BackboneMonitoringProvisioningRulesTest.php`
- `php artisan test --compact tests/Feature/BackboneMonitoringFoundationTest.php tests/Feature/BackboneMonitoringReadinessTest.php tests/Feature/BackboneMonitoringDashboardTest.php tests/Feature/BackboneMonitoringAlertRulesTest.php tests/Feature/BackboneMonitoringInspectionFilterTest.php tests/Feature/BackboneMonitoringProvisioningInspectionTest.php tests/Feature/BackboneMonitoringRollbackEvidenceTest.php`
- `php artisan test --compact` com PostgreSQL efêmero: `396 passed`, `1 skipped`, `2172 assertions`
- `vendor/bin/pint --dirty --format agent`
