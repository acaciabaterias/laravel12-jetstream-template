# Contract: Advanced Revenue Recovery Automation Events

## Objective

Definir os eventos materiais do módulo `020` para publicação, dispatch, violação e rollback da automação avançada de recuperação.

## Events

| Event | Version | Producer | Consumers | Purpose |
|-------|---------|----------|-----------|---------|
| `POLITICA_AUTOMACAO_RECUPERACAO_PUBLICADA` | `v1` | Módulo 020 | Backbone, Billing, Recovery, Executive Reporting | Sinaliza ativação controlada de uma nova política automatizada |
| `DISPATCH_AUTOMACAO_RECUPERACAO_EXECUTADO` | `v1` | Módulo 020 | Backbone, Recovery, MS-003 | Sinaliza dispatch automatizado concluído para uma jornada |
| `VIOLACAO_AUTOMACAO_RECUPERACAO_DETECTADA` | `v1` | Módulo 020 | Backbone, Observability, Executive Reporting | Sinaliza violação material ou degradação detectada |
| `ROLLBACK_AUTOMACAO_RECUPERACAO_EXECUTADO` | `v1` | Módulo 020 | Backbone, Billing, Recovery, Executive Reporting | Sinaliza reversão para política anterior saudável |

## Common Payload

- `policy_version_id`
- `policy_slug`
- `journey_id`
- `dispatch_id`
- `case_id`
- `variant_key`
- `channel`
- `status`
- `occurred_at`
- `metadata`

## Guarantees

- todo evento deve apontar para a versão de política material que tratou o caso
- dispatches automatizados não podem ser publicados como concluídos sem deduplicação validada
- rollback deve preservar referência à política substituída e à política restaurada
