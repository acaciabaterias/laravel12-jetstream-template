# Contract: Payment Events

## Purpose

Definir os eventos financeiros centrais mínimos publicados pelo módulo `012` para o backbone `010`.

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
| `COBRANCA_SAAS_EMITIDA` | `v1` | Módulo 012 | Plataforma, Analytics | Cobrança externa criada com sucesso |
| `COBRANCA_SAAS_FALHOU` | `v1` | Módulo 012 | Plataforma, MS-003 | Falha persistente de emissão |
| `COBRANCA_SAAS_LIQUIDADA` | `v1` | Módulo 012 | Módulo 011, Plataforma, Analytics | Recebimento confirmado |
| `COBRANCA_SAAS_EXPIRADA` | `v1` | Módulo 012 | Módulo 011, Plataforma | Cobrança externa venceu |
| `COBRANCA_SAAS_CANCELADA` | `v1` | Módulo 012 | Plataforma, Analytics | Cancelamento externo confirmado |
| `CONCILIACAO_SAAS_PENDENTE` | `v1` | Módulo 012 | Plataforma | Divergência aberta para análise |
| `CONCILIACAO_SAAS_RESOLVIDA` | `v1` | Módulo 012 | Plataforma, Analytics | Exceção encerrada |
| `COBRANCA_SAAS_ESTORNADA` | `v1` | Módulo 012 | Módulo 011, Plataforma | Estorno confirmado |
| `COBRANCA_SAAS_CHARGEBACK` | `v1` | Módulo 012 | Módulo 011, Plataforma | Chargeback confirmado |

## Compatibility policy

- Replays devem manter a versão original do evento.
- Eventos financeiros não devem expor segredos, tokens ou payload bruto sensível do gateway.
- Ajustes de conciliação manual devem gerar evento próprio quando alterarem o estado final da exceção ou da cobrança.
