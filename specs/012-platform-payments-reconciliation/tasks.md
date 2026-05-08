# Tasks: Módulo 012 - Platform Payments and Reconciliation

**Input**: Design documents from `/specs/012-platform-payments-reconciliation/`
**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/`

**Tests**: Every feature MUST include explicit test tasks. Tests are mandatory.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (`US1`, `US2`, `US3`)
- Include exact file paths in descriptions

## Path Conventions

- Laravel application code in `app/`
- Central migrations in `database/migrations/central/`
- HTTP routes in `routes/`
- Feature tests in `tests/Feature/`
- Unit tests in `tests/Unit/`

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare central payments namespace and feature baseline

- [x] T001 Create payments service namespace scaffolding in `app/Services/Billing/` and contracts updates in `app/Services/Contracts/`
- [x] T002 Create feature and unit test namespace baseline for platform payments in `tests/Feature/` and `tests/Unit/`
- [x] T003 [P] Register feature documentation references and central plan pointer consistency in `AGENTS.md` and `specs/012-platform-payments-reconciliation/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core central infrastructure required before any user story implementation

**⚠️ CRITICAL**: No user story work should begin until this phase is complete

- [x] T004 Create central migrations for `gateways_cobranca_saas`, `cobrancas_saas_externas`, `retornos_pagamento_saas`, `conciliacoes_pagamento_saas` and `excecoes_conciliacao_saas` in `database/migrations/central/`
- [x] T005 [P] Create Eloquent models `GatewayCobrancaSaaS`, `CobrancaSaaSExterna`, `RetornoPagamentoSaaS`, `ConciliacaoPagamentoSaaS` and `ExcecaoConciliacaoSaaS` in `app/Models/`
- [x] T006 [P] Create shared enums/value objects for external charge status, webhook processing and reconciliation outcomes in `app/Support/Billing/`
- [x] T007 Create authorization policy/gate baseline for super admin payment operations in `app/Policies/` and `app/Providers/AppServiceProvider.php`
- [x] T008 Create central payment configuration entries in `config/services.php` or dedicated payment config file for gateway profiles, idempotency and retry defaults
- [x] T009 Create foundational audit/event publication hooks for external charge and reconciliation state transitions in `app/Services/Billing/`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Cobrar faturas SaaS no gateway (Priority: P1) 🎯 MVP

**Goal**: Emit external SaaS charges with traceable linkage to central invoices and no duplicate issuance

**Independent Test**: A super admin can emit a SaaS charge for an eligible `FaturaSaaS` and obtain an external reference without creating duplicate obligations

### Tests for User Story 1 ⚠️

- [x] T010 [P] [US1] Create feature test for external charge issuance and linkage in `tests/Feature/PlatformPaymentsChargeIssuanceTest.php`
- [x] T011 [P] [US1] Create feature test for duplicate issuance prevention and controlled reissue in `tests/Feature/PlatformPaymentsDuplicateIssuanceTest.php`
- [x] T012 [P] [US1] Create unit test for external charge idempotency key generation in `tests/Unit/PlatformPaymentsIdempotencyTest.php`

### Implementation for User Story 1

- [x] T013 [P] [US1] Implement `GatewayRegistryService` in `app/Services/Billing/GatewayRegistryService.php`
- [x] T014 [P] [US1] Implement `ExternalChargeIssuanceService` in `app/Services/Billing/ExternalChargeIssuanceService.php`
- [x] T015 [US1] Implement central admin workflow or Livewire screen for charge issuance in `app/Livewire/Admin/`
- [x] T016 [US1] Implement requests/validation for gateway selection and controlled reissue in `app/Http/Requests/`
- [x] T017 [US1] Persist external issuance history and state transitions through central payment entities

**Checkpoint**: User Story 1 should emit and track SaaS charges independently

---

## Phase 4: User Story 2 - Conciliar retornos e baixar faturas automaticamente (Priority: P2)

**Goal**: Process external payment returns idempotently and settle central invoices safely

**Independent Test**: A payment webhook or return can be processed once, reconcile the correct invoice and update the commercial lifecycle without duplicate side effects

### Tests for User Story 2 ⚠️

- [x] T018 [P] [US2] Create feature test for webhook processing and automatic settlement in `tests/Feature/PlatformPaymentsWebhookSettlementTest.php`
- [x] T019 [P] [US2] Create feature test for duplicate or out-of-order webhook handling in `tests/Feature/PlatformPaymentsWebhookIdempotencyTest.php`
- [x] T020 [P] [US2] Create unit test for reconciliation safety rules in `tests/Unit/PlatformPaymentsReconciliationRuleTest.php`

### Implementation for User Story 2

- [x] T021 [P] [US2] Implement `PaymentWebhookIngestionService` in `app/Services/Billing/PaymentWebhookIngestionService.php`
- [x] T022 [P] [US2] Implement `PaymentReconciliationService` in `app/Services/Billing/PaymentReconciliationService.php`
- [x] T023 [US2] Implement job/command for asynchronous return processing and replay in `app/Jobs/` or `app/Console/Commands/`
- [x] T024 [US2] Integrate successful settlement and reversal signals with module `011` state management and related central checks
- [x] T025 [US2] Publish central financial events (`COBRANCA_SAAS_LIQUIDADA`, related events) through backbone `010`

**Checkpoint**: User Story 2 should reconcile valid returns independently

---

## Phase 5: User Story 3 - Operar divergências e exceções de reconciliação (Priority: P3)

**Goal**: Give super admins an operational panel for payment divergences, refunds and chargebacks

**Independent Test**: The platform can classify non-reconcilable payment events, expose them centrally and allow controlled operational follow-up

### Tests for User Story 3 ⚠️

- [x] T026 [P] [US3] Create feature test for payment operations dashboard visibility in `tests/Feature/PlatformPaymentsDashboardTest.php`
- [x] T027 [P] [US3] Create feature test for divergence filters and exception drill-down in `tests/Feature/PlatformPaymentsExceptionFiltersTest.php`
- [x] T028 [P] [US3] Create unit test for exception classification and severity aggregation in `tests/Unit/PlatformPaymentsExceptionClassifierTest.php`

### Implementation for User Story 3

- [x] T029 [P] [US3] Implement `PlatformPaymentsSummaryService` in `app/Services/Billing/PlatformPaymentsSummaryService.php`
- [x] T030 [US3] Implement super admin Livewire dashboard for payments and reconciliation health in `app/Livewire/Admin/PlatformPaymentsDashboard.php`
- [x] T031 [US3] Implement central filters, summaries and drill-down actions in dashboard views under `resources/views/livewire/admin/`
- [x] T032 [US3] Expose central payment inspection endpoints or reusable query services for operational support in `app/Http/Controllers/` or `app/Services/Billing/`

**Checkpoint**: User Story 3 should provide operational payment visibility independently

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T033 [P] Document payment rollback, webhook idempotency and reconciliation procedures in `GO_LIVE_RUNBOOK.md` and related operational docs
- [x] T034 Update architecture and product documentation for the new payments layer in `ARCHITECTURE.md`, `README.md` and roadmap artifacts when implementation starts
- [x] T035 [P] Add targeted coverage for payment event publication, exception classification and replay audit trail in `tests/Feature/` and `tests/Unit/`
- [x] T036 Perform code cleanup and Laravel Pint on changed files
- [x] T037 Run `quickstart.md` validation and record evidence in feature artifacts

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies
- **Foundational (Phase 2)**: Depends on Setup completion and blocks all user stories
- **User Stories (Phase 3-5)**: Depend on Foundational completion
- **Polish (Phase 6)**: Depends on desired user stories being complete

### User Story Dependencies

- **US1 (P1)**: Starts after Foundational and establishes external payment MVP
- **US2 (P2)**: Starts after Foundational and depends logically on charge issuance and `FaturaSaaS` state from `011`
- **US3 (P3)**: Starts after Foundational and can overlap late US2 work once exceptions and events exist

### Parallel Opportunities

- T005, T006 and T007 can run in parallel after migrations are planned
- All tests inside each user story marked `[P]` can run in parallel
- T013 and T014 can run in parallel before T015
- T021 and T022 can run in parallel before T023 and T024
- T029 and T032 can run in parallel before T030 and T031

## Implementation Strategy

### MVP First (US1 only)

1. Complete Setup
2. Complete Foundational phase
3. Deliver US1 with gateway issuance and duplicate prevention
4. Validate external payment linkage before adding webhook reconciliation

### Incremental Delivery

1. Add US1 for charge issuance and provider linkage
2. Add US2 for webhook processing, settlement and idempotency
3. Add US3 for super admin visibility and exception operations
4. Finish with documentation, audit refinement and quickstart validation

## Notes

- Tests must fail before implementation begins
- No task should move payment SaaS state into tenant databases
- Automatic settlement must stay conservative and auditable
- Backup/restore and rollback evidence must be updated when central payment state is introduced
