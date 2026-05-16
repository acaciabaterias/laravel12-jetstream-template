# Contract: Executive Reporting Events

## Objective

Definir os eventos materiais do módulo `019` para geração e reexecução auditável de relatórios executivos.

## Events

| Event | Version | Producer | Consumers | Purpose |
|-------|---------|----------|-----------|---------|
| `RELATORIO_EXECUTIVO_GERADO` | `v1` | Módulo 019 | Backbone, Observability, Analytics | Sinaliza que uma exportação executiva foi concluída |
| `RELATORIO_EXECUTIVO_REEXECUTADO` | `v1` | Módulo 019 | Backbone, Observability, Analytics | Sinaliza reexecução de relatório com contexto preservado |
| `RELATORIO_EXECUTIVO_FALHOU` | `v1` | Módulo 019 | Backbone, Observability | Sinaliza falha material na geração do relatório |

## Common Payload

- `report_slug`
- `snapshot_id`
- `export_id`
- `format`
- `period_start`
- `period_end`
- `filters`
- `operator`
- `status`
- `occurred_at`

## Guarantees

- eventos devem refletir apenas exportações com trilha auditável persistida
- reexecução deve apontar para a exportação original ou seu contexto equivalente
- falhas materiais devem preservar o escopo do relatório tentado
