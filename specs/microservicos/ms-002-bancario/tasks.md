# Tasks: MS-002 — Bancário (Boletos, PIX e CNAB)

**Identificador**: `MS-002-BANCARIO`
**Spec**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

---

## Phase 1: Setup e Infraestrutura

- [ ] T001: Criar repositório `ms-002-bancario` com estrutura base de pastas
- [ ] T002: Inicializar projeto Node.js (Fastify + TypeScript + Prisma)
- [ ] T003: Criar schema Prisma para `Cobranca`, `RemessaCNAB`, `BancoPerfil`, `WebhookRecebido`
- [ ] T004: Configurar `docker-compose.yml` com `ms-bancario-api`, `postgres`, `redis`
- [ ] T005: Configurar variáveis de ambiente e sistema de criptografia de credenciais (AES-256-GCM)
- [ ] T006: Criar tabela `BancoPerfil` seedada com os 5 bancos suportados em modo homologação

---

## Phase 2: Adapters Bancários

- [ ] T007: Criar interface `BancoAdapter.interface.ts` com contratos de todos os métodos
- [ ] T008: Implementar `BradescoAdapter.ts` (API Bradesco Developer — boleto + PIX + CNAB 400)
- [ ] T009: Implementar `ItauAdapter.ts` (API Itaú e-Commerce — boleto + PIX)
- [ ] T010: Implementar `SicoobAdapter.ts` (API Sicoob — PIX + CNAB 240)
- [ ] T011: Implementar `BancoDoBrasilAdapter.ts` (API BB Developers — boleto + PIX)
- [ ] T012: Implementar `CaixaAdapter.ts` (API CEF + CNAB 240)
- [ ] T013: Implementar `AdapterFactory.ts` — Fábrica que instancia o adapter correto por `banco_id`

---

## Phase 3: Serviços Core

- [ ] T014: Implementar `CobrancaService.ts` com lógica de idempotência por `idempotency_key`
- [ ] T015: Implementar `CnabService.ts` — parsing CNAB retorno (240/400) e geração de remessa
- [ ] T016: Implementar `WebhookService.ts` — validação HMAC por banco e processamento de payload
- [ ] T017: Implementar polling automático de status para bancos sem webhook (intervalo 15 minutos via BullMQ)

---

## Phase 4: API Routes

- [ ] T018: Implementar `POST /api/v1/boleto` com validação Zod e idempotência
- [ ] T019: Implementar `POST /api/v1/pix` com geração de QR Code e txid
- [ ] T020: Implementar `GET /api/v1/cobranca/{id}` para consulta de status
- [ ] T021: Implementar `DELETE /api/v1/cobranca/{id}` para baixa/cancelamento
- [ ] T022: Implementar `POST /api/v1/cnab/remessa` — geração e download de arquivo REM
- [ ] T023: Implementar `POST /api/v1/cnab/retorno` — upload e processamento de arquivo RET
- [ ] T024: Implementar `POST /api/v1/webhook/{banco}` com validação HMAC por banco

---

## Phase 5: Consumers de Eventos

- [ ] T025: Implementar `CobrancaCriarConsumer.ts` — escuta `COBRANCA_CRIAR_BOLETO` e `COBRANCA_CRIAR_PIX`
- [ ] T026: Implementar `CnabRetConsumer.ts` — escuta `CNAB_RET_PROCESSAR`
- [ ] T027: Implementar publicador de eventos de saída (`COBRANCA_CRIADA`, `COBRANCA_PAGA`, `COBRANCA_EXPIRADA`)
- [ ] T027A: Adaptar consumers, publishers e webhooks ao envelope canônico do Módulo 010 (`event_version`, `tenant_external_ref`, `correlation_id`, `causation_id`, `idempotency_key`)
- [ ] T027B: Implementar tratamento de retry, dead-letter e replay operacional compatível com o backbone para cobranças e webhooks

---

## Phase 6: Testes

- [ ] T028: Teste unitário `CnabService` — parsear arquivo CNAB 240 de exemplo e validar liquidações
- [ ] T029: Teste unitário `CobrancaService` — verificar idempotência (mesmo `idempotency_key` retorna cobrança existente)
- [ ] T030: Teste unitário `WebhookService` — simular webhook com HMAC válido e inválido
- [ ] T031: Teste de integração `BradescoAdapter` em ambiente de homologação
- [ ] T032: Teste E2E — boleto criado → webhook de pagamento recebido → evento `COBRANCA_PAGA` publicado
- [ ] T033: Teste de carga — 100 boletos gerados em paralelo sem falhas de idempotência
- [ ] T033A: Teste de performance validando latência em < 3s (p95) para boletos e < 1s para PIX (simulando APIs externas)
- [ ] T033B: Teste de contrato do envelope canônico e replay operacional contra o catálogo do Módulo 010

---

## Phase 7: Deploy e CI/CD

- [ ] T034: Criar `Dockerfile` multi-stage para a API Node.js
- [ ] T035: Configurar pipeline CI/CD com testes automatizados em ambiente de homologação
- [ ] T036: Configurar secrets de credenciais bancárias via Docker Secrets ou Vault
- [ ] T037: Criar dashboard Grafana para métricas de cobranças (geradas, pagas, falhas por banco)
- [ ] T037A: Registrar endpoints síncronos no gateway do Módulo 010 com timeout, autenticação e rastreio padronizado
- [ ] T038: Documentar processo de onboarding de novos bancos (criação de adapter + configuração de webhook)
- [ ] T039: Executar linting e formatação em todos os arquivos TypeScript modificados (ESLint + Prettier) antes de cada merge
