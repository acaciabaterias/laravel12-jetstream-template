# Contract: Benchmark Events

## Purpose

Definir os eventos centrais mínimos publicados pelo módulo `017` para o backbone `010`.

## Required envelope fields

- `event_type`
- `event_version`
- `tenant_external_ref`
- `correlation_id`
- `idempotency_key`
- `occurred_at`
- `payload`

## Initial event set

| Event Type | Version | Producer | Consumers | Notes |
|------------|---------|----------|-----------|-------|
| `BENCHMARK_REGRESSIVO_DETECTADO` | `v1` | Módulo 017 | Plataforma, Support | Regressão material de benchmark acima da tolerância |
| `GARGALO_CRITICO_IDENTIFICADO` | `v1` | Módulo 017 | Plataforma | Gargalo material em banco, fila, endpoint ou aplicação |
| `BASELINE_CARGA_PROMOVIDA` | `v1` | Módulo 017 | Plataforma | Nova baseline validada para cenário crítico |
| `ROLLBACK_PERFORMANCE_EXECUTADO` | `v1` | Módulo 017 | Plataforma, Support | Reversão material de tuning regressivo |

## Compatibility policy

- Eventos de benchmark não devem duplicar incidentes do módulo `015` sem vínculo de contexto.
- Mudanças em tolerâncias ou fluxos precisam preservar compatibilidade com `015` e `016`.
- Eventos de rollback devem manter referência auditável ao tuning e benchmark afetados.
