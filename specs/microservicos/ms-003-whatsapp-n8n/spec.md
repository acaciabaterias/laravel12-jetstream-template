# Microserviço Specification: MS-003 — WhatsApp & Notificações via n8n

**Identificador**: `MS-003-WHATSAPP-N8N`
**Status**: Ready for Implementation
**Tipo**: Microserviço Autônomo — Plataforma Low-Code (n8n + Evolution API)
**Dependências ERP**: Módulo 007 (Garantias), Módulo 005 (Vendas), Módulo 006 (Logística)

---

## Overview

O MS-003 é o hub central de **comunicação automatizada com clientes** via WhatsApp, e-mail e SMS. Ele é construído sobre **n8n** (plataforma de automação low-code/no-code, self-hosted) integrado com **Evolution API** (wrapper do WhatsApp Business API / WhatsApp Web não-oficial para uso interno).

**Modelo arquitetural:**
- O ERP publica eventos no broker (Redis/RabbitMQ)
- O **n8n escuta esses eventos via webhook ou Redis trigger**
- O n8n executa workflows visuais que decidem: qual template de mensagem usar, quais dados preencher, qual canal enviar (WhatsApp, e-mail, SMS)
- A **Evolution API** é a camada de transporte para o WhatsApp

**Por que n8n?**
- Workflows de notificação são frequentemente alterados pela equipe de negócio (templates de mensagem, regras de envio, horários)
- n8n permite que usuários não-técnicos alterem workflows sem deploy de código
- Reduz drasticamente o ciclo de atualização de templates de mensagem

---

## Key Entities (Persistência no n8n / banco próprio)

- **WorkflowExecucao**: (id, workflow_name, evento_trigger, status [success/error/waiting], payload_entrada, mensagem_enviada, canal, destinatario, created_at)
- **TemplateMensagem**: (id, nome, canal [whatsapp/email/sms], conteudo_template, variaveis, ativo)
- **FilaNotificacao**: (id, evento, destinatario, canal, payload, tentativas, status, agendado_para)
- **ContatoBlacklist**: (id, numero_tel, motivo, adicionado_em)

---

## Functional Requirements

### FR-003-01: Envio de Mensagens WhatsApp
- O MS DEVE receber eventos do ERP e disparar mensagens WhatsApp via Evolution API
- Mensagens DEVEM ser enviadas com templates pré-definidos (com variáveis substituídas)
- O MS DEVE suportar: texto, texto + imagem, texto + PDF (DANFE, boleto)
- Horário de envio DEVE respeitar a janela comercial (8h–20h). Mensagens fora do horário são agendadas

### FR-003-02: Notificação de Venda/Boleto
- Ao evento `VALE_FATURADO`: enviar confirmação de compra e linha digitável do boleto (se boleto)
- Ao evento `COBRANCA_PAGA`: enviar confirmação de pagamento ao cliente

### FR-003-03: Alertas de Garantia
- Ao evento `GARANTIA_VENCENDO` (Módulo 007): notificar cliente sobre vencimento próximo da garantia
- Ao evento `OS_CONCLUIDA` (Módulo 007): notificar cliente que o serviço foi concluído e o produto está pronto

### FR-003-04: Notificações de Entrega
- Ao evento `ENTREGA_SAIU` (Módulo 006): notificar cliente que o entregador saiu
- Ao evento `ENTREGA_CONCLUIDA` (Módulo 006): enviar confirmação de entrega e link de pesquisa NPS

### FR-003-05: Alertas Internos (para equipe)
- Certificado fiscal expirando (`CERTIFICADO_EXPIRANDO`): WhatsApp para responsável de TI
- Contingência fiscal crítica (`NF_CONTINGENCIA_CRITICA`): WhatsApp para supervisor fiscal
- Fatura vencida sem pagamento (D+3): WhatsApp para equipe de cobrança

### FR-003-06: Respostas Automáticas (Chatbot Básico)
- Quando cliente responde "SIM" ou "NÃO" a uma mensagem de confirmação, o MS DEVE interpretar e publicar evento de resposta no broker
- Fluxo de confirmação de entrega: cliente responde → evento `ENTREGA_CONFIRMADA` ou `ENTREGA_CONTESTADA` publicado

### FR-003-07: Blacklist e Opt-out
- Cliente pode responder "PARAR" ou "SAIR" → número adicionado à blacklist → mensagens futuras bloqueadas
- Blacklist gerenciada e consultável via API interna

---

## User Stories

### US-003-01: Confirmação de Compra Automática
**Como** cliente que acabou de comprar uma bateria,
**Quando** minha venda é faturada,
**Quero** receber uma mensagem no WhatsApp com o resumo da compra e o boleto,
**Para que** eu tenha o comprovante sem precisar solicitar.

**Critérios de Aceite:**
- Mensagem enviada em < 30 segundos após `VALE_FATURADO`
- PDF do DANFE ou linha digitável incluídos conforme forma de pagamento
- Número da nota fiscal e valor total na mensagem

### US-003-02: Aviso de Entrega em Tempo Real
**Como** cliente aguardando entrega,
**Quando** o entregador sai da loja,
**Quero** receber uma mensagem informando que a bateria está a caminho,
**Para que** eu possa me organizar para receber.

**Critérios de Aceite:**
- Mensagem enviada em < 2 minutos após `ENTREGA_SAIU`
- Nome do entregador e ETA aproximado incluídos
- Link de acompanhamento (se disponível via Módulo 006)

### US-003-03: Alerta de Garantia com Ação
**Como** cliente com bateria na garantia,
**Quando** faltam 30 dias para o vencimento,
**Quero** receber um aviso no WhatsApp,
**Para que** eu possa verificar e agir antes que a garantia expire.

**Critérios de Aceite:**
- Mensagem enviada com botão de resposta "VERIFICAR" que aciona fluxo de agendamento
- Data exata de vencimento e número da OS incluídos

### US-003-04: Opt-out Respeitado
**Como** cliente que não deseja receber mensagens,
**Quando** respondo "PARAR",
**Quero** que nenhuma mensagem futura seja enviada para meu número,
**Para que** minha privacidade seja respeitada.

**Critérios de Aceite:**
- Número adicionado à blacklist em < 5 segundos após resposta
- Confirmação de opt-out enviada como última mensagem
- Nenhuma mensagem enviada após confirmação

---

## Eventos

### Eventos que o MS-003 **ESCUTA**:

| Evento | Publicado por | Ação do Workflow |
|---|---|---|
| `VALE_FATURADO` | Módulo 005 / 009 | Confirmar compra + boleto/PIX |
| `COBRANCA_PAGA` | MS-002 | Confirmar pagamento recebido |
| `ENTREGA_SAIU` | Módulo 006 | Aviso de saída para entrega |
| `ENTREGA_CONCLUIDA` | Módulo 006 | Pesquisa NPS pós-entrega |
| `GARANTIA_VENCENDO` | Módulo 007 | Lembrete de garantia |
| `OS_CONCLUIDA` | Módulo 007 | Produto pronto para retirada |
| `CERTIFICADO_EXPIRANDO` | MS-001 | Alerta interno TI |
| `NF_CONTINGENCIA_CRITICA` | MS-001 | Alerta interno fiscal |

### Eventos que o MS-003 **PUBLICA**:

| Evento | Consumido por | Descrição |
|---|---|---|
| `WHATSAPP_ENVIADO` | Módulo 009 (log) | Mensagem entregue ao destinatário |
| `WHATSAPP_FALHOU` | Módulo 009 | Falha no envio (número inválido, bloqueio) |
| `ENTREGA_CONFIRMADA` | Módulo 006 | Cliente confirmou recebimento via WhatsApp |
| `ENTREGA_CONTESTADA` | Módulo 006 | Cliente recusou ou contestou entrega via WhatsApp |
| `CLIENTE_OPT_OUT` | Módulo CRM | Cliente solicitou parar envios |

---

## API Endpoints (REST Interno)

| Método | Endpoint | Descrição |
|---|---|---|
| `POST` | `/api/v1/notificacao/enviar` | Disparo manual de notificação |
| `GET` | `/api/v1/notificacao/historico/{cliente_id}` | Histórico de mensagens por cliente |
| `GET` | `/api/v1/fila` | Lista notificações na fila (pendentes/agendadas) |
| `POST` | `/api/v1/blacklist` | Adiciona número à blacklist |
| `DELETE` | `/api/v1/blacklist/{numero}` | Remove número da blacklist |
| `GET` | `/api/v1/blacklist` | Lista blacklist |
| `GET` | `/api/v1/templates` | Lista templates de mensagem |
| `GET` | `/api/v1/health` | Health check (n8n + Evolution API) |
| `POST` | `/webhook/evolution` | Recebe webhooks da Evolution API (respostas de clientes) |
| `POST` | `/webhook/erp/{evento}` | Trigger direto de workflow por evento (alternativa ao broker) |

---

## Edge Cases

- **Número de WhatsApp inválido ou inexistente**: Evolution API retorna erro → publicar `WHATSAPP_FALHOU` + tentar envio por e-mail como fallback (se disponível)
- **Cliente na blacklist**: Verificar blacklist antes de qualquer envio. Ignorar silenciosamente e logar tentativa bloqueada
- **Mensagem fora do horário comercial**: Armazenar na `FilaNotificacao` com `agendado_para` = próximo dia útil às 8h
- **Evolution API fora do ar**: Retry com backoff (3 tentativas: 1min, 5min, 15min). Após falhar, publicar `WHATSAPP_FALHOU`
- **Template com variável ausente**: Validar variáveis antes de enviar. Se variável crítica ausente, logar erro e não enviar mensagem incompleta
- **Flood de mensagens (mesmo cliente, múltiplos eventos simultâneos)**: Rate limiting por cliente: máximo 3 mensagens em 10 minutos. Demais são enfileiradas

---

## Success Criteria

- **SC-003-01**: Mensagem de confirmação de compra enviada em < 30 segundos após faturamento
- **SC-003-02**: 100% das solicitações de opt-out ("PARAR") processadas em < 5 segundos
- **SC-003-03**: Nenhuma mensagem enviada fora do horário comercial (8h–20h)
- **SC-003-04**: Taxa de entrega de mensagens (WhatsApp) ≥ 95% para números válidos
- **SC-003-05**: Alterações de template de mensagem aplicáveis sem deploy de código (pelo n8n)
- **SC-003-06**: 100% dos eventos críticos internos (certificado, contingência) geram alerta em < 60 segundos
