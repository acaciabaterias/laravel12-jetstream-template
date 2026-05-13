# Contract: Monitoring Events

## Purpose

Definir os eventos centrais mínimos publicados pelo módulo `016` para o backbone `010`.

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
| `MONITORAMENTO_DEGRADADO` | `v1` | Módulo 016 | Plataforma, Support | Degradação relevante da malha externa de observabilidade |
| `SCRAPE_HEALTH_CRITICO` | `v1` | Módulo 016 | Plataforma | Falha ou atraso crítico de coleta em target monitorado |
| `DASHBOARD_MONITORAMENTO_ATUALIZADO` | `v1` | Módulo 016 | Plataforma | Pacote de dashboards/alertas provisionado ou validado |
| `ROLLBACK_MONITORAMENTO_EXECUTADO` | `v1` | Módulo 016 | Plataforma, Support | Rollback material de painel ou regra de alerta |

## Compatibility policy

- Eventos de monitoramento não devem duplicar semanticamente incidentes do módulo `015` sem vínculo de contexto.
- Mudanças na taxonomia de fluxo ou severidade precisam preservar compatibilidade com `010` e `015`.
- Eventos de rollback devem manter referência auditável ao pacote e ambiente afetados.
