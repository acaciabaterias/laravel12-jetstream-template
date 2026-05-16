# Contract: Billing Events

## Purpose

Definir os eventos comerciais mínimos publicados pelo módulo `011` para o backbone `010`.

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
| `ASSINATURA_ATIVADA` | `v1` | Módulo 011 | MS-003, Plataforma | Assinante ativado |
| `PLANO_ALTERADO` | `v1` | Módulo 011 | Plataforma, Analytics | Migração comercial |
| `FATURA_SAAS_VENCEU` | `v1` | Módulo 011 | MS-003, Plataforma | Acionamento de cobrança |
| `GRACE_PERIOD_INICIADO` | `v1` | Módulo 011 | MS-003, Plataforma | Tolerância iniciada |
| `ASSINANTE_BLOQUEADO` | `v1` | Módulo 011 | Plataforma, MS-003 | Bloqueio efetivado |
| `ASSINANTE_REATIVADO` | `v1` | Módulo 011 | Plataforma, MS-003 | Regularização confirmada |
| `ASSINATURA_CANCELADA` | `v1` | Módulo 011 | Plataforma, Analytics | Encerramento contratual |

## Compatibility policy

- Replays devem manter a versão original do evento.
- Eventos de cobrança não devem expor segredos, tokens ou dados sensíveis de pagamento.
- Mudanças de política não devem invalidar eventos históricos já publicados.
