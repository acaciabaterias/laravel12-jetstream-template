# Contract: Event Catalog

## Purpose

Definir o catálogo canônico de eventos entre ERP e microserviços para o backbone `010`.

## Required contract fields

- `event_type`
- `event_version`
- `producer`
- `consumers`
- `idempotency_key_strategy`
- `correlation_fields`
- `minimum_payload`
- `failure_policy`

## Initial event set

| Event Type | Version | Producer | Consumers | Notes |
|------------|---------|----------|-----------|-------|
| `VALE_FATURADO` | `v1` | Módulo 005/009 | MS-001, MS-003 | Base para emissão fiscal e notificação |
| `COBRANCA_CRIAR_BOLETO` | `v1` | Módulo 009 | MS-002 | Geração de cobrança bancária |
| `COBRANCA_PAGA` | `v1` | MS-002 | Módulo 008, MS-003 | Baixa financeira e comunicação |
| `TRANSACOES_CAPTURADAS` | `v1` | MS-004 | Módulo 008 | Alimentação de conciliação |
| `ROTA_CRIADA` | `v1` | Módulo 006 | MS-005 | Otimização de rota |
| `ROTA_OTIMIZADA` | `v1` | MS-005 | Módulo 006 | Retorno operacional para entrega |

## Compatibility policy

- Novas versões não substituem implicitamente versões ativas.
- Payloads depreciados devem continuar identificáveis durante a janela de transição.
- Replay deve carregar a versão original do evento.
