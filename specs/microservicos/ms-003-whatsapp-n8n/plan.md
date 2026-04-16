# Implementation Plan: MS-003 — WhatsApp & Notificações via n8n

**Identificador**: `MS-003-WHATSAPP-N8N`
**Spec**: [spec.md](spec.md)
**Repositório**: `ms-003-whatsapp-n8n` (projeto separado do ERP)

---

## Constitution Check

> Requisito da constitution v1.5.0 — Quality Gate 1 e 2: *"Every implementation plan MUST include a Constitution Check. Constitution check gates in planning MUST pass before implementation begins."*

| Functional Requirement | Princípio da Constitution | Status | Notas |
|---|---|---|---|
| FR-003-01: Envio de Mensagens WhatsApp | **V. Proactive Quality & Customer Service** — "automate customer notifications via WhatsApp for status updates" | ✅ Alinhado | Este MS é a implementação exata do requisito de notificação automatizada via WhatsApp |
| FR-003-02: Notificação de Venda/Boleto | **V. Proactive Quality & Customer Service** + **I. Business Domain Specialization** — notificação específica do domínio de revenda de baterias | ✅ Alinhado | |
| FR-003-03: Alertas de Garantia | **V. Proactive Quality & Customer Service** — "manage product guarantees… automate customer notifications" | ✅ Alinhado | Complementa o Módulo 007 (Garantias) |
| FR-003-04: Notificações de Entrega | **II. Mobile-First Field Operations** — "seamless integration between field and in-store operations" | ✅ Alinhado | Notificação ao cliente fecha o ciclo da operação de campo |
| FR-003-05: Alertas Internos para Equipe | **VI. Integrated Fiscal Compliance** (alertas de certificado/contingência) + **I. Business Domain Specialization** | ✅ Alinhado | |
| FR-003-06: Respostas Automáticas (Chatbot) | **V. Proactive Quality & Customer Service** — "improve customer satisfaction, enhance after-sales support" | ✅ Alinhado | |
| FR-003-07: Blacklist e Opt-out | **V. Proactive Quality & Customer Service** — respeito ao cliente e LGPD | ✅ Alinhado | |

**Princípios sem conflito identificado:** III, IV — não impactados diretamente por este MS.

**Stack Tecnológica (Quality Gate — Technology Stack Constraints):**
- n8n (self-hosted, >= 1.40.0): ✅ Serviço autônomo. Justificativa explícita: workflows de notificação mudam frequentemente por decisão de negócio; n8n permite alteração sem deploy de código, reduzindo cycle time de dias para minutos. Aprovado como exceção arquitetural ao stack canônico.
- Evolution API v2: ✅ Wrapper para WhatsApp Business — necessário pois não há solução no stack canônico Laravel 12 para transporte WhatsApp
- PostgreSQL: ✅ Stack canônico
- Redis: ✅ Stack canônico

---

## Stack Tecnológica

| Camada | Tecnologia | Justificativa |
|---|---|---|
| **Automação / Workflows** | n8n (self-hosted, v1.x) | Low-code, workflows editáveis sem deploy, comunidade ativa |
| **WhatsApp Transport** | Evolution API v2 | Wrapper estável do WhatsApp Web, suporte a múltiplas instâncias |
| **Broker (trigger)** | Redis Pub/Sub ou RabbitMQ | n8n tem node nativo de Redis/RabbitMQ como trigger de workflow |
| **Banco de Dados** | PostgreSQL 15+ (n8n usa internamente) | n8n armazena execuções de workflow no Postgres |
| **Banco adicional** | PostgreSQL (schema separado) | Blacklist, histórico de notificações, templates |
| **Scheduler** | n8n cron nodes | Agendamento de mensagens fora do horário |
| **API Gateway fino** | Node.js (Express/Fastify) | Proxy leve para endpoints REST não cobertos pelo n8n |

---

## Padrão de Comunicação

```
ERP (qualquer módulo)
    └── Redis Broker
            ├── PUBLICA: VALE_FATURADO    →  n8n Redis Trigger → Workflow "Confirmação Compra"
            ├── PUBLICA: ENTREGA_SAIU     →  n8n Redis Trigger → Workflow "Aviso Entrega"
            ├── PUBLICA: GARANTIA_VENCENDO →  n8n Redis Trigger → Workflow "Alerta Garantia"
            └── [outros eventos...]

n8n Workflows
    └── Evolution API (HTTP)
            └── WhatsApp Business (entrega)

Evolution API
    └── Webhook → n8n Webhook node → Workflow "Processar Resposta Cliente"
            └── Publica evento de volta ao Redis
```

---

## n8n — Configuração e Workflows

### Configuração do n8n (Docker)

```yaml
# docker-compose.yml
services:
  n8n:
    image: n8nio/n8n:latest
    environment:
      - N8N_BASIC_AUTH_ACTIVE=true
      - N8N_BASIC_AUTH_USER=admin
      - N8N_BASIC_AUTH_PASSWORD=${N8N_PASSWORD}
      - DB_TYPE=postgresdb
      - DB_POSTGRESDB_HOST=postgres
      - DB_POSTGRESDB_DATABASE=n8n
      - N8N_HOST=n8n.seudominio.com.br
      - WEBHOOK_URL=https://n8n.seudominio.com.br/
      - EXECUTIONS_DATA_PRUNE=true
      - EXECUTIONS_DATA_MAX_AGE=720  # 30 dias
    volumes:
      - n8n_data:/home/node/.n8n
      - ./workflows:/home/node/.n8n/workflows  # backup de workflows exportados
    ports:
      - "5678:5678"
    depends_on:
      - postgres
      - redis

  evolution-api:
    image: atendai/evolution-api:v2
    environment:
      - SERVER_URL=https://evolution.seudominio.com.br
      - AUTHENTICATION_API_KEY=${EVOLUTION_API_KEY}
      - DATABASE_ENABLED=true
      - DATABASE_CONNECTION_URI=postgresql://...
      - WEBHOOK_GLOBAL_URL=https://n8n.seudominio.com.br/webhook/evolution
      - WEBHOOK_GLOBAL_ENABLED=true
    ports:
      - "8080:8080"
    volumes:
      - evolution_data:/evolution/instances

  postgres:
    image: postgres:15-alpine
    environment:
      POSTGRES_DB: n8n
      POSTGRES_USER: n8n
      POSTGRES_PASSWORD: ${POSTGRES_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./init.sql:/docker-entrypoint-initdb.d/init.sql  # schema adicional

  redis:
    image: redis:7-alpine
```

### Catálogo de Workflows n8n

| Workflow | Trigger | Descrição |
|---|---|---|
| `wf-confirmacao-compra` | Redis: `VALE_FATURADO` | Monta mensagem com dados da venda e envia via Evolution |
| `wf-confirm-pagamento` | Redis: `COBRANCA_PAGA` | Notifica cliente que pagamento foi confirmado |
| `wf-aviso-entrega-saiu` | Redis: `ENTREGA_SAIU` | Avisa cliente que entregador saiu com ETA |
| `wf-pesquisa-nps` | Redis: `ENTREGA_CONCLUIDA` | Envia pesquisa de satisfação com opções de resposta |
| `wf-alerta-garantia` | Redis: `GARANTIA_VENCENDO` | Lembrete de garantia próxima do vencimento |
| `wf-os-concluida` | Redis: `OS_CONCLUIDA` | Produto pronto → notifica cliente |
| `wf-alerta-interno-cert` | Redis: `CERTIFICADO_EXPIRANDO` | WhatsApp para grupo de TI |
| `wf-alerta-contingencia` | Redis: `NF_CONTINGENCIA_CRITICA` | WhatsApp urgente para fiscal |
| `wf-processar-fila` | Schedule (Cron, 15 min) | Lê tabela `FilaNotificacao` para horários vencidos e envia |
| `wf-processar-resposta` | Webhook: Evolution API | Interpreta respostas do cliente (SIM/NÃO/PARAR) |
| `wf-opt-out` | Nó filho de `wf-processar-resposta` | Adiciona à blacklist + publica `CLIENTE_OPT_OUT` |

### Estrutura de um Workflow (exemplo conceitual)

```
[Redis Trigger: VALE_FATURADO]
    → [Function: Extrair dados do payload]
    → [HTTP: Consultar dados do cliente no ERP]
    → [IF: Número na blacklist?]
        └── SIM → [Stop + Log]
        └── NÃO → [IF: Horário comercial?]
            └── NÃO → [Schedule: Agendar para 8h próximo dia útil]
            └── SIM → [HTTP: Enviar via Evolution API]
                    → [IF: Sucesso?]
                        └── SIM → [Redis Publish: WHATSAPP_ENVIADO]
                        └── NÃO → [Retry 3x] → [Redis Publish: WHATSAPP_FALHOU]
```

---

## Evolution API — Configuração

- **Instâncias**: Criar uma instância de WhatsApp por CNPJ/filial
- **QR Code**: Conexão inicial requer scan de QR Code pela responsável da empresa
- **Reconexão automática**: Evolution API tenta reconectar automaticamente se a sessão cair
- **Webhook de respostas**: Evolution envia mensagens recebidas para o n8n via `WEBHOOK_GLOBAL_URL`

---

## Estrutura de Pastas

```
ms-003-whatsapp-n8n/
├── docker-compose.yml
├── docker-compose.prod.yml
├── .env.example
├── init.sql                    # Schema adicional (blacklist, templates, logs)
├── workflows/                  # Workflows exportados do n8n em JSON
│   ├── wf-confirmacao-compra.json
│   ├── wf-aviso-entrega.json
│   ├── wf-alerta-garantia.json
│   ├── wf-pesquisa-nps.json
│   ├── wf-processar-resposta.json
│   └── [demais workflows...]
├── api/                        # Proxy API leve (Node.js) para endpoints extras
│   ├── src/
│   │   ├── routes/
│   │   │   ├── notificacao.routes.ts
│   │   │   ├── blacklist.routes.ts
│   │   │   └── health.routes.ts
│   │   └── server.ts
│   └── Dockerfile
└── docs/
    └── onboarding-whatsapp.md  # Guia de configuração inicial (QR Code, instâncias)
```

---

## Rate Limiting e Controle de Flood

```sql
-- Verificado antes de cada envio no workflow n8n (via HTTP node para API)
-- Regra: máximo 3 mensagens por cliente em 10 minutos
SELECT COUNT(*) FROM notificacao_log
WHERE cliente_id = $1
  AND canal = 'whatsapp'
  AND created_at > NOW() - INTERVAL '10 minutes';
```

---

## Monitoramento e Alertas

| Métrica | Threshold |
|---|---|
| Taxa de falha de envio WhatsApp | > 10% em 1h |
| Fila de notificações agendadas | > 500 pendentes |
| Execuções de workflow com erro | > 5 em 15 minutos |
| Evolution API desconectada | Qualquer desconexão → alerta imediato |
| Opt-outs por hora | > 20/h (possível problema de spam) |

---

## Segurança

- n8n protegido por autenticação básica + acesso restrito por IP (nginx upstream)
- Evolution API Key armazenada em variável de ambiente, nunca em workflows exportados
- Números de clientes mascarados nos logs de auditoria (LGPD)
- Blacklist consultada em TODA tentativa de envio (sem exceção)
