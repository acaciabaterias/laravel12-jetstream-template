# Tasks: MS-004 — Open Finance (Extratos e Conciliação)

**Identificador**: `MS-004-OPENFINANCE`
**Spec**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

---

## Phase 1: Setup e Infraestrutura

- [ ] T001: Criar repositório `ms-004-openfinance` com estrutura base de pastas
- [ ] T002: Inicializar projeto Python (FastAPI + SQLAlchemy 2.0 + Alembic)
- [ ] T003: Criar `docker-compose.yml` com serviços: `ms-openfinance-api`, `postgres`, `redis`
- [ ] T004: Criar Alembic migrations para tabelas: `consentimentos`, `transacoes_bancarias`, `extrato_captura_log`, `banco_providers`
- [ ] T005: Configurar variáveis de ambiente `.env.example` (encryption key, provider client IDs, Redis URL, etc.)
- [ ] T006: Implementar sistema de criptografia AES-256-GCM para tokens OAuth (`CryptographyService.py`)
- [ ] T007: Seeder de `BancoProvider` com Pluggy e Belvo em modo sandbox

---

## Phase 2: Adapters de Providers

- [ ] T008: Criar classe abstrata `FinanceProviderAdapter(ABC)` com contratos de interface
- [ ] T009: Implementar `PluggyAdapter` — autenticação Pluggy API + listagem de transações
- [ ] T010: Implementar `BelvoAdapter` — autenticação Belvo API + listagem de transações
- [ ] T011: Implementar `NormalizacaoService` — converte formatos específicos dos providers para `TransacaoNormalizada`
- [ ] T012: Implementar `AdapterFactory` — retorna adapter correto baseado em `BancoProvider.provider`

---

## Phase 3: Fluxo OAuth e Consentimentos

- [ ] T013: Implementar `GET /api/v1/oauth/authorize/{banco}` — gera URL de autorização e armazena `state` no Redis
- [ ] T014: Implementar `GET /api/v1/oauth/callback` — troca authorization code por tokens, persiste criptografado + publicar evento `CONSENTIMENTO_ATIVO` no broker com `consentimento_id` após persistência bem-sucedida
- [ ] T015: Implementar refresh automático de token (verificado antes de cada captura, margem de 10 minutos)
- [ ] T016: Implementar `ConsentimentoService.listar_ativos()` e `marcar_expirado()`
- [ ] T017: Implementar job de verificação de consentimentos a vencer (cron diário → publica `CONSENTIMENTO_EXPIRANDO` para quem vence em 7 dias)

---

## Phase 4: Captura de Extratos

- [ ] T018: Implementar `CapturaService.capturar(consentimento)` — orquestra busca, normalização, deduplicação e publica evento `TRANSACOES_CAPTURADAS` (sucesso) ou `CAPTURA_ERRO` (falha após retries máximos)
- [ ] T019: Implementar deduplicação por `deduplicacao_hash` com janela de 30 dias
- [ ] T020: Implementar retry com `tenacity` (3 tentativas, backoff exponencial 30s/2min/10min)
- [ ] T021: Implementar registro em `ExtratoCapturaLog` ao final de cada captura (success/error/partial)
- [ ] T022: Implementar lock distribuído via Redis para evitar execuções simultâneas do mesmo consentimento
- [ ] T022A: Adaptar eventos, callbacks OAuth e capturas ao envelope canônico do Módulo 010 (`event_version`, `tenant_external_ref`, `correlation_id`, `causation_id`, `idempotency_key`)
- [ ] T022B: Implementar dead-letter e replay operacional compatíveis com o backbone para capturas e callbacks

---

## Phase 5: Scheduler (Cron Job)

- [ ] T023: Configurar APScheduler com job às 00h, 04h, 08h, 12h, 16h e 20h
- [ ] T024: Garantir que falhas individuais não impedem captura dos demais consentimentos (`asyncio.gather(..., return_exceptions=True)`)
- [ ] T025: Implementar `ExtratoManualConsumer` — escuta `EXTRATO_CAPTURAR_MANUAL` para capturas on-demand

---

## Phase 6: API Routes

- [ ] T026: Implementar `GET /api/v1/consentimentos` com filtro por `empresa_id`
- [ ] T027: Implementar `DELETE /api/v1/consentimentos/{id}` com revogação no provider
- [ ] T028: Implementar `POST /api/v1/extratos/capturar/{consentimento_id}` para captura on-demand
- [ ] T029: Implementar `GET /api/v1/transacoes` com filtros (consentimento_id, data_de, data_ate, tipo)
- [ ] T030: Implementar `GET /api/v1/captura/logs` com histórico de execuções do cron

---

## Phase 7: Testes

- [ ] T031: Teste unitário `NormalizacaoService` — dado payload Pluggy mockado, verificar campos normalizados
- [ ] T032: Teste unitário `CapturaService` — verificar deduplicação (segunda captura não duplica transações)
- [ ] T033: Teste unitário criptografia — token criptografado e descriptografado corretamente, não aparece em texto plano no banco
- [ ] T034: Teste de integração `PluggyAdapter` em ambiente sandbox Pluggy
- [ ] T035: Teste E2E OAuth → Captura → Publicação: `CONSENTIMENTO_ATIVO` → cron dispara → `TRANSACOES_CAPTURADAS` publicado
- [ ] T036: Teste de falha de provider: Pluggy retorna 503 → retry executado → `CAPTURA_ERRO` publicado após 3 falhas
- [ ] T036A: Teste E2E cronometrado garantindo que o job cron de capturas consiga processar o batch de consentimentos dentro da janela alvo de < 2 min
- [ ] T036B: Teste de contrato do envelope canônico e replay operacional contra o catálogo do Módulo 010

---

## Phase 8: Deploy e CI/CD

- [ ] T037: Criar `Dockerfile` para API FastAPI (multi-stage, imagem slim)
- [ ] T038: Configurar pipeline CI/CD com testes em ambiente sandbox dos providers
- [ ] T039: Configurar Prometheus metrics (transações capturadas/h, taxa de deduplicação, latência por provider)
- [ ] T039A: Registrar endpoints síncronos no gateway do Módulo 010 com timeout, autenticação e rastreio padronizado
- [ ] T040: Documentar processo de onboarding de novo banco (criação de BancoProvider + adapter)
- [ ] T041: Criar runbook para renovação manual de consentimento expirado
- [ ] T042: Executar linting e formatação em todos os arquivos Python modificados (Black + isort + Ruff) antes de cada merge
