# Tasks: Módulo 008 - Financeiro Inteligente

**Feature Branch**: `008-intelligent-finance`
**Spec File**: [spec.md](spec.md)

## Constitution Traceability

- **Multi-Tenancy Isolado (v2.0.0)**: T001-T007, T012-T030
- **Automated Financial Microservices**: T008-T018, T024-T029
- **Proactive Quality & Customer Service**: T015-T018, T024-T030
- **RBAC**: T007, T019-T023, T027-T030

## Phase 1: Database Migrations (Tenant)

- [x] T001: Criar migration `create_contas_bancarias_table`
- [x] T002: Criar migration `create_transacoes_financeiras_table`
- [x] T003: Criar migration `create_fluxo_caixa_projetado_table`
- [x] T004: Criar migration `create_margens_lucro_reais_table`
- [x] T005: Criar migration `create_conciliacoes_pendentes_table`
- [x] T006: Criar migration `create_fechamentos_contabeis_table`
- [x] T007: Criar migration `create_audit_logs_table`

## Phase 2: Models, Services e Jobs

- [x] T008: Criar Model `ContaBancaria`
- [x] T009: Criar Model `TransacaoFinanceira`
- [x] T010: Criar Model `FluxoCaixaProjetado`
- [x] T011: Criar Model `MargemLucroReal`
- [x] T012: Criar Model `ConciliacaoPendente`
- [x] T013: Criar Model `FechamentoContabil`
- [x] T014: Criar service `BankApiClient`
- [x] T015: Criar service `FinanceMatcherProcessor`
- [x] T016: Criar service `ClosingPeriodGuard`
- [x] T017: Criar Trait `Auditable`
- [x] T018: Criar job `SyncBankTransactionsJob`

## Phase 3: Painéis e Fluxos Financeiros

- [x] T019: Criar Livewire component `FinanceDashboard`
- [x] T020: Criar Livewire component `CashFlowPanel`
- [x] T021: Criar Livewire component `MarginAnalysisGrid`
- [x] T022: Implementar lançamentos manuais com auditoria
- [x] T023: Implementar painel de pendências de conciliação

## Phase 4: Integrações e Gatilhos

- [x] T024: Implementar importação e matching de transações bancárias
- [x] T025: Implementar atualização do fluxo de caixa projetado
- [x] T026: Implementar apuração de margem de lucro real
- [x] T027: Implementar geração automática de cobrança para OS improcedente
- [x] T028: Implementar bloqueio de alterações em período contábil fechado

## Phase 5: Tests

- [x] T029: Testar conciliação automática com matches simples
- [x] T030: Testar transações ambíguas indo para pendência manual
- [x] T031: Testar fluxo de caixa projetado
- [x] T032: Testar margem de lucro real por produto
- [x] T033: Testar cobrança automática de improcedência
- [x] T034: Testar bloqueio de edição em competência fechada
- [x] T035: Testar auditoria financeira das operações críticas
- [x] T036: Testar isolamento entre tenants sem cross-access
