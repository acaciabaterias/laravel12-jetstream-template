# Quickstart: Módulo 019 - Executive Reporting Hub

## Objetivo

Validar localmente o hub executivo central, seus filtros analíticos expandidos e a geração auditável de relatórios em Excel e PDF após a consolidação dos módulos `010` a `018`.

## Pré-requisitos

- banco central configurado
- módulos `011` a `014` operacionais com dados centrais coerentes
- backbone `010` operacional
- permissões super admin válidas
- snapshots comerciais já disponíveis para reuso

## Sequência sugerida

1. Acessar o dashboard executivo central com usuário super admin.
2. Aplicar filtros por período, plano, canal, carteira e status de recuperação.
3. Validar os principais KPIs e abrir pelo menos um drill-down operacional.
4. Gerar exportação Excel a partir do recorte ativo.
5. Gerar exportação PDF a partir do mesmo recorte.
6. Consultar o histórico central de exportações e confirmar operador, filtros e formatos.
7. Reexecutar uma exportação anterior usando o contexto salvo.
8. Confirmar publicação dos eventos materiais no backbone `010`.

## Cenários de validação

- Recalcular KPIs executivos sem divergência material ao trocar filtros.
- Exibir drill-down reutilizável para um indicador principal.
- Gerar Excel e PDF coerentes com o mesmo snapshot analítico.
- Bloquear exportação quando o contexto estiver inconsistente.
- Reexecutar relatório com trilha auditável preservada.

## Critérios para avançar à implementação completa

- dashboard executivo com filtros e drill-down coerentes
- exportações Excel e PDF reproduzindo o mesmo recorte
- histórico auditável de geração e reexecução
- inspeção central do snapshot e dos relatórios gerados
- runbook operacional cobrindo smoke e reexecução

## Evidência de validação esperada

- `git diff --check`
- testes direcionados do dashboard executivo, exportações e reexecução
- evidência de consistência entre dashboard e relatórios nos artefatos do módulo

## Registro de validação

- status: concluido localmente em 2026-05-16
- comandos executados:
  - `vendor/bin/pint --dirty --format agent`
  - `DB_CONNECTION=central DB_CENTRAL_DRIVER=pgsql DB_CENTRAL_HOST=127.0.0.1 DB_CENTRAL_PORT=55432 DB_CENTRAL_DATABASE=ci_central DB_CENTRAL_USERNAME=postgres DB_CENTRAL_PASSWORD=postgres DB_TENANT_DRIVER=pgsql DB_TENANT_HOST=127.0.0.1 DB_TENANT_PORT=55433 DB_TENANT_DATABASE=ci_tenant DB_TENANT_USERNAME=postgres DB_TENANT_PASSWORD=postgres php artisan test --compact tests/Feature/ExecutiveReportingDashboardTest.php tests/Feature/ExecutiveReportingDrilldownTest.php tests/Feature/ExecutiveReportingExcelExportTest.php tests/Feature/ExecutiveReportingPdfExportTest.php tests/Feature/ExecutiveReportingInspectionTest.php tests/Feature/ExecutiveReportingReexecutionTest.php tests/Feature/ExecutiveReportingGovernanceTest.php tests/Unit/ExecutiveReportingFilterRulesTest.php tests/Unit/ExecutiveReportingExportRulesTest.php tests/Unit/ExecutiveReportingExecutionRulesTest.php tests/Unit/ExecutiveReportingSnapshotSerializationTest.php`
  - `git diff --check`
- resultado observado:
  - `12` testes aprovados
  - `59` assertions
  - cobertura direcionada adicional para publicacao de evento material, serializacao de snapshot e trilha auditavel de exportacao
