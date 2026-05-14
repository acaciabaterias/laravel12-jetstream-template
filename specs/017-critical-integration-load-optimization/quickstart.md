# Quickstart: Módulo 017 - Critical Integration Load Optimization

## Objetivo

Validar localmente a camada central de benchmark, gargalo e tuning operacional após a consolidação dos módulos `010` a `016`.

## Pré-requisitos

- banco central configurado
- backbone `010` operacional
- módulos `015` e `016` concluídos com taxonomia estável
- stack de filas e endpoints críticos acessíveis no ambiente de validação
- cenários mínimos de benchmark definidos para os fluxos priorizados

## Sequência sugerida

1. Criar perfis de carga para backbone, payments, recovery, analytics e observability.
2. Registrar uma execução baseline por cenário com throughput, latência e erro.
3. Persistir gargalos identificados por categoria operacional.
4. Registrar um tuning candidate associado ao benchmark baseline.
5. Reexecutar benchmark de validação e classificar ganho ou regressão.
6. Promover a baseline validada ou registrar rollback recomendado com evidência.
7. Confirmar publicação de eventos materiais de regressão ou rollback no backbone `010`.

## Cenários de validação

- Detectar benchmark regressivo por latência, throughput ou erro.
- Diferenciar gargalo de banco, fila, endpoint externo e aplicação.
- Vincular tuning aplicado à hipótese e ao resultado observado.
- Registrar rollback de performance com baseline restaurada.
- Consultar inspeção reutilizável com cenários, benchmarks, gargalos e mudanças.

## Critérios para avançar à implementação completa

- ao menos um cenário reproduzível por fluxo crítico priorizado
- classificação de gargalo explícita e reutilizável
- tuning e rollback com trilha auditável por ambiente
- baseline promovida apenas após benchmark validado
- runbook operacional cobrindo revalidação e reversão

## Evidência de validação esperada

- `git diff --check`
- testes direcionados de benchmark, gargalo, tuning e rollback
- evidência de baseline validada e rollback registrada nos artefatos do módulo

## Evidência executada

- `git diff --check`
- `php artisan test --compact tests/Unit/CriticalLoadComparisonRulesTest.php tests/Unit/CriticalLoadBottleneckRulesTest.php tests/Unit/CriticalLoadTuningRulesTest.php`
- `php artisan test --compact tests/Feature/CriticalLoadFoundationTest.php tests/Feature/CriticalLoadBenchmarkRecordingTest.php tests/Feature/CriticalLoadOptimizationDashboardTest.php tests/Feature/CriticalLoadBottleneckInspectionTest.php tests/Feature/CriticalLoadInspectionFilterTest.php tests/Feature/CriticalLoadTuningInspectionTest.php tests/Feature/CriticalLoadRollbackEvidenceTest.php`
- `vendor/bin/pint --dirty --format agent`

Resultado consolidado do recorte:

- unitário: `4 passed`, `8 assertions`
- feature PostgreSQL do módulo `017`: `10 passed`, `44 assertions`
- baseline promovida, gargalo categorizado, tuning validado e rollback auditável confirmados no dashboard `/admin/capacity` e na inspeção `/admin/capacity/inspection`
