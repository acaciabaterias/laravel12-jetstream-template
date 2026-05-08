# Contract: Analytics Events

## Purpose

Definir os eventos centrais mínimos publicados pelo módulo `014` para o backbone `010`.

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
| `SNAPSHOT_ANALYTICS_ATUALIZADO` | `v1` | Módulo 014 | Plataforma, Analytics | Snapshot executivo recalculado |
| `COORTE_COMERCIAL_ATUALIZADA` | `v1` | Módulo 014 | Plataforma | Recortes de coorte reconstruídos |
| `INSIGHT_RISCO_IDENTIFICADO` | `v1` | Módulo 014 | Plataforma, MS-003 | Grupo de contas em risco comercial relevante |
| `CANAL_PERFORMANCE_DEGRADADO` | `v1` | Módulo 014 | Plataforma | Queda relevante em conversão de cobrança ou recovery |

## Compatibility policy

- Rebuilds devem manter semântica estável entre versões do snapshot.
- Eventos analíticos não devem expor payload sensível de clientes ou gateways.
- Mudanças de fórmula devem ser refletidas via versionamento explícito quando alterarem leitura histórica.
