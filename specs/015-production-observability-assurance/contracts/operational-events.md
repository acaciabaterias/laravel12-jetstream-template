# Contract: Operational Events

## Purpose

Definir os eventos centrais mínimos publicados pelo módulo `015` para o backbone `010`.

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
| `INCIDENTE_OPERACIONAL_ABERTO` | `v1` | Módulo 015 | Plataforma, Support | Incidente relevante aberto |
| `SERVICO_DEGRADADO_DETECTADO` | `v1` | Módulo 015 | Plataforma | Degradação persistente acima de limiar |
| `BASELINE_CARGA_ATUALIZADO` | `v1` | Módulo 015 | Plataforma | Baseline operacional revisado |
| `SERVICO_RECUPERADO` | `v1` | Módulo 015 | Plataforma, Analytics | Recuperação operacional confirmada |

## Compatibility policy

- Eventos operacionais não devem vazar dados sensíveis de tenant além do necessário para correlação.
- Mudanças em critérios de severidade devem ser refletidas em versionamento quando alterarem interpretação histórica.
- Recuperação de serviço deve manter vínculo auditável com o incidente relacionado quando existir.
