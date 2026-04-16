# Tasks: MS-001 — Fiscal (SEFAZ) via ACBr

**Identificador**: `MS-001-FISCAL-ACBR`
**Spec**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

---

## Phase 1: Setup e Infraestrutura

- [ ] T001: Criar repositório `ms-001-fiscal-acbr` com estrutura base de pastas definida no plan.md
- [ ] T002: Configurar `docker-compose.yml` com serviços: `acbr-server`, `ms-fiscal-api`, `redis`, `postgres`
- [ ] T003: Configurar imagem Docker do ACBr Server (testar connectivity local com SEFAZ homologação)
- [ ] T004: Criar `acbr.ini.template` com todas as configurações necessárias (UF, ambiente, versão NFe/NFCe)
- [ ] T005: Inicializar projeto Node.js (Fastify + TypeScript + Prisma)
- [ ] T006: Criar schema Prisma para `NotaFiscalJob`, `ContingenciaQueue`, `DanfeStorage`, `AuditLog`
- [ ] T007: Configurar variáveis de ambiente (`.env.example`) com todas as configs necessárias
- [ ] T008: Configurar conexão Redis (BullMQ) e testar pub/sub básico

---

## Phase 2: ACBr Wrapper

- [ ] T009: Implementar `AcbrService.ts` — client HTTP/TCP que se comunica com o ACBr container
- [ ] T010: Implementar método `emitirNFe(payload: NFePayload): Promise<AcbrResponse>` no `AcbrService`
- [ ] T011: Implementar método `emitirNFCe(payload: NFCePayload): Promise<AcbrResponse>` no `AcbrService`
- [ ] T012: Implementar método `cancelarNFe(chaveAcesso, justificativa): Promise<AcbrResponse>`
- [ ] T013: Implementar método `enviarCCe(chaveAcesso, correcao): Promise<AcbrResponse>`
- [ ] T014: Implementar método `inutilizar(params): Promise<AcbrResponse>`
- [ ] T015: Implementar `CertificadoService.ts` com verificação de validade e publicação de alertas

---

## Phase 3: API Routes

- [ ] T016: Implementar `POST /api/v1/nfe/emitir` com validação de schema (Zod) e idempotência por `correlation_id`
- [ ] T017: Implementar `POST /api/v1/nfce/emitir` com validação de schema e idempotência
- [ ] T018: Implementar `POST /api/v1/nfe/cancelar` com validação de prazo legal antes de chamar ACBr
- [ ] T019: Implementar `POST /api/v1/nfe/cce` com validação de campos permitidos para CC-e
- [ ] T020: Implementar `GET /api/v1/nfe/{chave_acesso}` para consulta de status
- [ ] T021: Implementar `GET /api/v1/contingencia/fila` para listagem de notas pendentes
- [ ] T022: Implementar `GET /api/v1/certificado/status` com dias restantes
- [ ] T023: Implementar `GET /api/v1/health` com status do ACBr container e Redis

---

## Phase 4: Consumers de Eventos (Message Broker)

- [ ] T024: Implementar `ValeFaturadoConsumer.ts` — escuta `VALE_FATURADO`, chama `EmissaoService`
- [ ] T025: Implementar `NfCancelarConsumer.ts` — escuta `NF_CANCELAR`, chama cancelamento via ACBr
- [ ] T026: Implementar `NfCceConsumer.ts` — escuta `NF_CCE_SOLICITAR`
- [ ] T027: Implementar publicador de eventos de saída (`NF_AUTORIZADA`, `NF_ERRO`, `NF_EM_CONTINGENCIA`, `NF_CANCELADA`)

---

## Phase 5: Fila de Contingência

- [ ] T028: Implementar `ContingenciaQueue.ts` com BullMQ e delays de backoff configurados (1min/5min/30min/2h/6h)
- [ ] T029: Implementar worker da fila que retransmite notas e publica `NF_AUTORIZADA` ao ter sucesso
- [ ] T030: Implementar lógica de limite absoluto: após 10 falhas ou 24h, publicar `NF_CONTINGENCIA_CRITICA`
- [ ] T031: Implementar `EmissaoService.ts` que orquestra: tentar emissão → detectar erro SEFAZ → enfileirar contingência

---

## Phase 6: Testes

- [ ] T032: Teste unitário `AcbrService` — mockar respostas do ACBr container e validar mapeamento
- [ ] T033: Teste unitário `EmissaoService` — simular SEFAZ offline e verificar que nota vai para contingência
- [ ] T034: Teste unitário `CertificadoService` — simular certificado com 25 dias de validade e verificar evento publicado
- [ ] T035: Teste de integração `POST /api/v1/nfe/emitir` contra ACBr em modo homologação SEFAZ
- [ ] T036: Teste de idempotência — enviar mesmo `correlation_id` duas vezes, verificar que apenas 1 emissão ocorre
- [ ] T037: Teste E2E da fila de contingência: simular SEFAZ fora do ar → notas enfileiradas → SEFAZ volta → notas transmitidas

---

## Phase 7: Deploy e CI/CD

- [ ] T038: Criar `Dockerfile` otimizado (multi-stage build) para a API Node.js
- [ ] T039: Criar `docker-compose.prod.yml` com configurações de produção (restart policies, resource limits)
- [ ] T040: Configurar pipeline CI/CD (GitHub Actions): lint → build → testes → push Docker image
- [ ] T041: Configurar Prometheus metrics endpoint + Grafana dashboard template para monitoramento
- [ ] T042: Documentar processo de rotação de certificado A1 (sem downtime)
- [ ] T043: Criar runbook de troubleshooting para os cenários de contingência
