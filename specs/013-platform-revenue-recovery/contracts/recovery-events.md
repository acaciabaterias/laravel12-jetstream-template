# Contract: Recovery Events

## Purpose

Definir os eventos centrais mínimos publicados pelo módulo `013` para o backbone `010`.

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
| `RECUPERACAO_RECEITA_INICIADA` | `v1` | Módulo 013 | Plataforma, Analytics | Caso de recuperação aberto |
| `ACAO_COBRANCA_AGENDADA` | `v1` | Módulo 013 | MS-003, Plataforma | Próxima ação automatizada definida |
| `CASO_RECUPERACAO_ESCALADO` | `v1` | Módulo 013 | Plataforma | Caso elevado para intervenção humana |
| `PROMESSA_PAGAMENTO_REGISTRADA` | `v1` | Módulo 013 | Plataforma, Analytics | Compromisso manual ativo |
| `PROMESSA_PAGAMENTO_QUEBRADA` | `v1` | Módulo 013 | Plataforma | Acordo expirou sem regularização |
| `RECUPERACAO_RECEITA_CONFIRMADA` | `v1` | Módulo 013 | Módulo 011, Analytics | Caso encerrado por regularização |
| `REENGAJAMENTO_ASSINANTE_INICIADO` | `v1` | Módulo 013 | MS-003, Plataforma | Fluxo de retenção/reengajamento aberto |

## Compatibility policy

- Replays devem manter a versão original do evento.
- Eventos de recuperação não devem expor payload sensível de contato nem dados brutos do gateway.
- Mudanças de política não devem invalidar eventos históricos já emitidos.
