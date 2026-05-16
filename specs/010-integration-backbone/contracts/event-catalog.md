# Contract: Event Catalog

## Purpose

Definir o catálogo canônico de eventos entre ERP e microserviços para o backbone `010`.

## Required contract fields

- `event_type`
- `event_version`
- `tenant_external_ref`
- `producer`
- `consumers`
- `correlation_id`
- `causation_id`
- `idempotency_key_strategy`
- `correlation_fields`
- `minimum_payload`
- `failure_policy`

## Envelope baseline

Todos os eventos do backbone `010` devem trafegar com envelope operacional mínimo:

- `event_type`
- `event_version`
- `tenant_external_ref`
- `correlation_id`
- `causation_id` quando houver encadeamento
- `idempotency_key`
- `occurred_at`
- `payload`

## Initial event set

| Event Type | Version | Producer | Consumers | Notes |
|------------|---------|----------|-----------|-------|
| `VALE_FATURADO` | `v1` | Módulo 005/009 | MS-001, MS-003 | Base para emissão fiscal e notificação |
| `NF_CANCELAR` | `v1` | Módulo 009 | MS-001 | Solicitação de cancelamento fiscal |
| `NF_CCE_SOLICITAR` | `v1` | Módulo 009 | MS-001 | Carta de correção |
| `NF_INUTILIZAR` | `v1` | Módulo 009 | MS-001 | Inutilização de numeração |
| `NF_AUTORIZADA` | `v1` | MS-001 | Módulo 009, Módulo 005 | Retorno fiscal autorizado |
| `NF_EM_CONTINGENCIA` | `v1` | MS-001 | Módulo 009 | Contingência ativa |
| `NF_CANCELADA` | `v1` | MS-001 | Módulo 009, Módulo 005 | Cancelamento confirmado |
| `NF_ERRO` | `v1` | MS-001 | Módulo 009 | Falha fiscal permanente |
| `NF_CONTINGENCIA_CRITICA` | `v1` | MS-001 | Módulo 009, MS-003 | Alerta operacional crítico |
| `CERTIFICADO_EXPIRANDO` | `v1` | MS-001 | MS-003, Sistema de Alertas | Certificado próximo do vencimento |
| `CERTIFICADO_EXPIRADO` | `v1` | MS-001 | Módulo 009, Sistema de Alertas | Certificado inválido |
| `COBRANCA_CRIAR_BOLETO` | `v1` | Módulo 009 | MS-002 | Geração de cobrança bancária |
| `COBRANCA_CRIAR_PIX` | `v1` | Módulo 009 | MS-002 | Geração de cobrança PIX |
| `COBRANCA_CANCELAR` | `v1` | Módulo 009, Módulo 008 | MS-002 | Baixa ou cancelamento |
| `CNAB_RET_PROCESSAR` | `v1` | Módulo 009 | MS-002 | Processamento de retorno |
| `COBRANCA_CRIADA` | `v1` | MS-002 | Módulo 009, Módulo 008 | Cobrança criada |
| `COBRANCA_PAGA` | `v1` | MS-002 | Módulo 008, MS-003 | Baixa financeira e comunicação |
| `COBRANCA_EXPIRADA` | `v1` | MS-002 | Módulo 008 | Cobrança vencida |
| `COBRANCA_CANCELADA` | `v1` | MS-002 | Módulo 008 | Baixa confirmada |
| `CNAB_RET_PROCESSADO` | `v1` | MS-002 | Módulo 009 | Resultado do retorno CNAB |
| `ENTREGA_SAIU` | `v1` | Módulo 006 | MS-003 | Aviso de saída para entrega |
| `ENTREGA_CONCLUIDA` | `v1` | Módulo 006 | MS-003 | Pesquisa pós-entrega |
| `GARANTIA_VENCENDO` | `v1` | Módulo 007 | MS-003 | Lembrete ao cliente |
| `OS_CONCLUIDA` | `v1` | Módulo 007 | MS-003 | Produto pronto ou serviço concluído |
| `WHATSAPP_ENVIADO` | `v1` | MS-003 | Módulo 009 | Log operacional de envio |
| `WHATSAPP_FALHOU` | `v1` | MS-003 | Módulo 009 | Falha de comunicação |
| `ENTREGA_CONFIRMADA` | `v1` | MS-003 | Módulo 006 | Confirmação do cliente |
| `ENTREGA_CONTESTADA` | `v1` | MS-003 | Módulo 006 | Contestação do cliente |
| `CLIENTE_OPT_OUT` | `v1` | MS-003 | CRM | Bloqueio de comunicações |
| `CONSENTIMENTO_ATIVO` | `v1` | MS-004 | Módulo 008 | Consentimento autorizado |
| `CONSENTIMENTO_EXPIRANDO` | `v1` | MS-004 | Módulo 008, MS-003 | Renovação iminente |
| `CONSENTIMENTO_EXPIRADO` | `v1` | MS-004 | Módulo 008 | Consentimento revogado ou expirado |
| `CAPTURA_ERRO` | `v1` | MS-004 | Módulo 008 | Falha persistente na captura |
| `EXTRATO_CAPTURAR_MANUAL` | `v1` | Módulo 008 | MS-004 | Captura on-demand |
| `TRANSACOES_CAPTURADAS` | `v1` | MS-004 | Módulo 008 | Alimentação de conciliação |
| `ROTA_CRIADA` | `v1` | Módulo 006 | MS-005 | Otimização de rota |
| `LOCALIZACAO_ATUALIZADA` | `v1` | App Entregador | MS-005 | Recalcular ETAs |
| `PARADA_STATUS_ATUALIZADO` | `v1` | App Entregador | MS-005 | Status de parada |
| `ROTA_OTIMIZADA` | `v1` | MS-005 | Módulo 006, App Entregador | Retorno operacional para entrega |
| `ETA_ATUALIZADO` | `v1` | MS-005 | App Entregador, Módulo 006 | ETA recalculado |
| `GEOCODIFICACAO_BAIXA_CONFIANCA` | `v1` | MS-005 | App Entregador | Ajuste manual necessário |
| `LIMITE_API_ATINGINDO` | `v1` | MS-005 | Sistema de Alertas | Cota geográfica próxima do limite |

## Compatibility policy

- Novas versões não substituem implicitamente versões ativas.
- Payloads depreciados devem continuar identificáveis durante a janela de transição.
- Replay deve carregar a versão original do evento.
