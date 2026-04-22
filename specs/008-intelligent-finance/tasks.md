# Tasks: Módulo 008 - Financeiro Inteligente

**Feature Branch**: `008-intelligent-finance`
**Spec File**: [spec.md](spec.md)

## Constitution Traceability

- **Multi-Tenancy Isolado (v2.0.0)**: T001-T007, T012-T030
- **Automated Financial Microservices**: T008-T018, T024-T029
- **Proactive Quality & Customer Service**: T015-T018, T024-T030
- **RBAC**: T007, T019-T023, T027-T030

## Phase 1: Database Migrations (Tenant)

- [ ] T001: Criar migration `create_contas_bancarias_table`
- [ ] T002: Criar migration `create_transacoes_financeiras_table`
- [ ] T003: Criar migration `create_fluxo_caixa_projetado_table`
- [ ] T004: Criar migration `create_margens_lucro_reais_table`
- [ ] T005: Criar migration `create_conciliacoes_pendentes_table`
- [ ] T006: Criar migration `create_fechamentos_contabeis_table`
- [ ] T007: Criar migration `create_audit_logs_table`

## Phase 2: Models, Services e Jobs

- [ ] T008: Criar Model `ContaBancaria`
- [ ] T009: Criar Model `TransacaoFinanceira`
- [ ] T010: Criar Model `FluxoCaixaProjetado`
- [ ] T011: Criar Model `MargemLucroReal`
- [ ] T012: Criar Model `ConciliacaoPendente`
- [ ] T013: Criar Model `FechamentoContabil`
- [ ] T014: Criar service `BankApiClient`
- [ ] T015: Criar service `FinanceMatcherProcessor`
- [ ] T016: Criar service `ClosingPeriodGuard`
- [ ] T017: Criar Trait `Auditable`
- [ ] T018: Criar job `SyncBankTransactionsJob`

## Phase 3: Painéis e Fluxos Financeiros

- [ ] T019: Criar Livewire component `FinanceDashboard`
- [ ] T020: Criar Livewire component `CashFlowPanel`
- [ ] T021: Criar Livewire component `MarginAnalysisGrid`
- [ ] T022: Implementar lançamentos manuais com auditoria
- [ ] T023: Implementar painel de pendências de conciliação

## Phase 4: Integrações e Gatilhos

- [ ] T024: Implementar importação e matching de transações bancárias
- [ ] T025: Implementar atualização do fluxo de caixa projetado
- [ ] T026: Implementar apuração de margem de lucro real
- [ ] T027: Implementar geração automática de cobrança para OS improcedente
- [ ] T028: Implementar bloqueio de alterações em período contábil fechado

## Phase 5: Tests

- [ ] T029: Testar conciliação automática com matches simples
- [ ] T030: Testar transações ambíguas indo para pendência manual
- [ ] T031: Testar fluxo de caixa projetado
- [ ] T032: Testar margem de lucro real por produto
- [ ] T033: Testar cobrança automática de improcedência
- [ ] T034: Testar bloqueio de edição em competência fechada
- [ ] T035: Testar auditoria financeira das operações críticas
- [ ] T036: Testar isolamento entre tenants sem cross-access
