# Tasks: MS-003 — WhatsApp & Notificações via n8n

**Identificador**: `MS-003-WHATSAPP-N8N`
**Spec**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

> **Nota**: Por ser uma plataforma low-code, a maioria das tarefas é de **configuração de infraestrutura, importação de workflows e configuração de integrações**, não de código tradicional. O código customizado está na API proxy e nos scripts utilitários.

---

## Phase 1: Setup de Infraestrutura

- [ ] T001: Criar repositório `ms-003-whatsapp-n8n` com estrutura base de pastas
- [ ] T002: Criar `docker-compose.yml` com serviços: `n8n`, `evolution-api`, `postgres`, `redis`
- [ ] T003: Configurar variáveis de ambiente `.env.example` com todas as configs (n8n auth, Evolution API key, webhook URLs)
- [ ] T004: Criar `init.sql` com schema adicional: tabelas `notificacao_log`, `blacklist`, `template_mensagem`, `fila_notificacao`
- [ ] T005: Subir stack em ambiente local e verificar que n8n está acessível em `http://localhost:5678`
- [ ] T006: Verificar que `postgres` tem os dois bancos necessários: `n8n` (interno) e `ms003` (schema adicional)

---

## Phase 2: Configuração do Evolution API (WhatsApp)

- [ ] T007: Configurar Evolution API com URL pública e API Key
- [ ] T008: Criar instância WhatsApp `bateria-expert-principal` na Evolution API
- [ ] T009: Gerar QR Code e conectar número WhatsApp da empresa (scan manual pelo responsável)
- [ ] T010: Configurar `WEBHOOK_GLOBAL_URL` apontando para o n8n (`/webhook/evolution`)
- [ ] T011: Testar envio manual de mensagem de teste via Evolution API REST
- [ ] T012: Verificar que respostas recebidas chegam ao n8n via webhook

---

## Phase 3: Configuração do n8n

- [ ] T013: Acessar interface n8n e configurar credenciais: Redis, PostgreSQL, Evolution API (HTTP Header Auth)
- [ ] T014: Configurar nó de trigger Redis no n8n com conexão ao broker do ERP

---

## Phase 4: Importação e Configuração dos Workflows

- [ ] T015: Criar e importar workflow `wf-confirmacao-compra` (trigger: `VALE_FATURADO` → Evolution API send)
- [ ] T016: Criar e importar workflow `wf-confirm-pagamento` (trigger: `COBRANCA_PAGA`)
- [ ] T017: Criar e importar workflow `wf-aviso-entrega-saiu` (trigger: `ENTREGA_SAIU`)
- [ ] T018: Criar e importar workflow `wf-pesquisa-nps` (trigger: `ENTREGA_CONCLUIDA`)
- [ ] T019: Criar e importar workflow `wf-alerta-garantia` (trigger: `GARANTIA_VENCENDO`)
- [ ] T020: Criar e importar workflow `wf-os-concluida` (trigger: `OS_CONCLUIDA`)
- [ ] T021: Criar e importar workflow `wf-alerta-interno-cert` (trigger: `CERTIFICADO_EXPIRANDO`)
- [ ] T022: Criar e importar workflow `wf-alerta-contingencia` (trigger: `NF_CONTINGENCIA_CRITICA`)
- [ ] T023: Criar e importar workflow `wf-processar-resposta` (trigger: webhook Evolution) com lógica de SIM/NÃO/PARAR
- [ ] T024: Criar sub-workflow `wf-opt-out` acionado pelo `wf-processar-resposta`
- [ ] T025: Exportar todos os workflows como JSON para a pasta `workflows/` (versionamento em Git)
- [ ] T025A: Padronizar triggers e publicações dos workflows com o envelope canônico do Módulo 010 (`event_version`, `tenant_external_ref`, `correlation_id`, `causation_id`, `idempotency_key`)

---

## Phase 5: Lógicas Transversais nos Workflows

- [ ] T026: Implementar verificação de blacklist em todos os workflows de saída (request HTTP para API proxy antes de enviar)
- [ ] T027: Implementar verificação de horário comercial (8h–20h) com agendamento via n8n Schedule node
- [ ] T028: Implementar rate limiting por cliente (máximo 3 msgs em 10 minutos) via query no banco
- [ ] T029: Implementar lógica de retry em todos os workflows (3 tentativas com backoff via n8n Wait node)
- [ ] T030: Implementar log de auditoria ao final de cada workflow (insert em `notificacao_log`)
- [ ] T030A: Implementar dead-letter e replay operacional para eventos de notificação e webhooks da Evolution API compatíveis com o backbone

---

## Phase 6: API Proxy (Node.js)

- [ ] T031: Inicializar projeto Node.js (Fastify + TypeScript) na pasta `api/`
- [ ] T032: Implementar `GET /api/v1/notificacao/historico/{cliente_id}` consultando `notificacao_log`
- [ ] T033: Implementar `POST /api/v1/blacklist` e `DELETE /api/v1/blacklist/{numero}`
- [ ] T034: Implementar `GET /api/v1/templates` listando templates ativos
- [ ] T034A: Implementar `POST /api/v1/templates` e `PUT /api/v1/templates/{id}` para CRUD de templates via API
- [ ] T035: Implementar `POST /api/v1/notificacao/enviar` para disparo manual (publica evento no Redis)
- [ ] T036: Implementar `GET /api/v1/health` verificando n8n e Evolution API

---

## Phase 7: Testes

- [ ] T037: Teste E2E workflow `wf-confirmacao-compra`: publicar evento `VALE_FATURADO` no Redis → verificar mensagem recebida no WhatsApp de teste
- [ ] T038: Teste de blacklist: número na blacklist → verificar que nenhuma mensagem é enviada
- [ ] T039: Teste de horário fora do comercial: publicar evento às 23h → verificar agendamento correto para próxima manhã
- [ ] T040: Teste de opt-out: enviar "PARAR" → verificar inserção na blacklist + confirmação enviada
- [ ] T041: Teste de falha Evolution API: simular instância desconectada → verificar retry e publicação de `WHATSAPP_FALHOU`
- [ ] T042: Testar exportação e reimportação de todos os workflows do JSON (garantir reprodutibilidade)
- [ ] T042A: Teste de contrato do envelope canônico e replay operacional contra o catálogo do Módulo 010

---

## Phase 8: Deploy e Documentação

- [ ] T043: Criar `docker-compose.prod.yml` com restart policies, logs e TLS
- [ ] T044: Configurar domínio e certificado SSL (nginx reverse proxy para n8n e Evolution API)
- [ ] T045: Criar `docs/onboarding-whatsapp.md` com passo-a-passo de conexão da instância (QR Code)
- [ ] T046: Criar `docs/adicionar-workflow.md` com guia para adicionar novos eventos/workflows
- [ ] T047: Configurar backup automático do banco n8n e volume de workflows (crontab ou script)
- [ ] T048: Configurar monitoramento: alerta no Slack/WhatsApp próprio se Evolution API desconectar
- [ ] T048A: Configurar Grafana Dashboard calculando Taxa de Entrega (Mensagens Enviadas vs Confirmadas ≥ 95%)
- [ ] T048B: Registrar endpoints síncronos e webhooks no gateway do Módulo 010 com autenticação, timeout e rastreio padronizado
- [ ] T049: Executar linting e formatação em todos os arquivos TypeScript da API proxy (ESLint + Prettier) e validar JSON dos workflows exportados antes de cada merge
