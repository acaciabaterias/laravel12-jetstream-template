# Tasks: Módulo 013 - Platform Revenue Recovery

**Input**: Design documents from `/specs/013-platform-revenue-recovery/`
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

**Purpose**: Prepare central revenue recovery namespace and feature baseline

- [x] T001 Create recovery service namespace scaffolding in `app/Services/Billing/` and related contracts updates in `app/Services/Contracts/`
- [x] T002 Create feature and unit test namespace baseline for platform revenue recovery in `tests/Feature/` and `tests/Unit/`
- [x] T003 [P] Register feature documentation references and central plan pointer consistency in `AGENTS.md` and `specs/013-platform-revenue-recovery/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core central infrastructure required before any user story implementation

**⚠️ CRITICAL**: No user story work should begin until this phase is complete

- [x] T004 Create central migrations for `politicas_recuperacao_receita`, `casos_recuperacao_receita`, `acoes_recuperacao_receita`, `compromissos_pagamento` and snapshots/structures for indicators in `database/migrations/central/`
- [x] T005 [P] Create Eloquent models `PoliticaRecuperacaoReceita`, `CasoRecuperacaoReceita`, `AcaoRecuperacaoReceita`, `CompromissoPagamento` and indicator model(s) in `app/Models/`
- [x] T006 [P] Create shared enums/value objects for case status, action type, action status, promise status and severity in `app/Support/Billing/`
- [x] T007 Create authorization policy/gate baseline for super admin and billing recovery operations in `app/Policies/` and `app/Providers/AppServiceProvider.php`
- [x] T008 Create central recovery configuration entries in `config/services.php` or dedicated recovery config for stage timing, replay windows and escalation defaults
- [x] T009 Create foundational audit/event publication hooks for recovery case and action transitions in `app/Services/Billing/`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Disparar régua de cobrança após falha ou atraso (Priority: P1) 🎯 MVP

**Goal**: Start and progress a central revenue recovery case automatically after overdue invoices or payment failures

**Independent Test**: A billing operator can evaluate an eligible overdue or failed invoice and obtain one recovery case with the correct first action and no duplicate entry

### Tests for User Story 1 ⚠️

- [x] T010 [P] [US1] Create feature test for opening a recovery case from overdue invoice state in `tests/Feature/PlatformRevenueRecoveryOpenCaseTest.php`
- [x] T011 [P] [US1] Create feature test for failed payment signal reusing an existing case without duplication in `tests/Feature/PlatformRevenueRecoveryDeduplicationTest.php`
- [x] T012 [P] [US1] Create unit test for stage-and-channel idempotency key generation in `tests/Unit/PlatformRevenueRecoveryIdempotencyTest.php`

### Implementation for User Story 1

- [x] T013 [P] [US1] Implement `RevenueRecoveryPolicyService` in `app/Services/Billing/RevenueRecoveryPolicyService.php`
- [x] T014 [P] [US1] Implement `RevenueRecoveryCaseService` in `app/Services/Billing/RevenueRecoveryCaseService.php`
- [x] T015 [US1] Implement `RevenueRecoveryActionScheduler` in `app/Services/Billing/RevenueRecoveryActionScheduler.php`
- [x] T016 [US1] Implement commands/jobs for asynchronous evaluation of overdue and failed-payment candidates in `app/Console/Commands/` and `app/Jobs/`
- [x] T017 [US1] Persist recovery case history and first-stage action transitions through the new central entities

**Checkpoint**: User Story 1 should open and schedule recovery independently

---

## Phase 4: User Story 2 - Escalonar contas críticas e registrar compromissos (Priority: P2)

**Goal**: Escalate critical cases and preserve promises of payment with selective suspension of conflicting actions

**Independent Test**: A billing manager can escalate a critical case, assign ownership and register a promise of payment that temporarily suspends incompatible automated actions

### Tests for User Story 2 ⚠️

- [x] T018 [P] [US2] Create feature test for automatic escalation of critical or recurrent cases in `tests/Feature/PlatformRevenueRecoveryEscalationTest.php`
- [x] T019 [P] [US2] Create feature test for promise-of-payment registration and selective suspension in `tests/Feature/PlatformRevenueRecoveryPromiseTest.php`
- [x] T020 [P] [US2] Create unit test for escalation scoring and suspension rules in `tests/Unit/PlatformRevenueRecoveryRulesTest.php`

### Implementation for User Story 2

- [x] T021 [P] [US2] Implement `RevenueRecoveryEscalationService` in `app/Services/Billing/RevenueRecoveryEscalationService.php`
- [x] T022 [P] [US2] Implement `PaymentPromiseService` in `app/Services/Billing/PaymentPromiseService.php`
- [x] T023 [US2] Implement admin workflow or Livewire screen for ownership, escalation and promise recording in `app/Livewire/Admin/`
- [x] T024 [US2] Implement requests/validation for promise creation, escalation and manual follow-up in `app/Http/Requests/`
- [x] T025 [US2] Publish central recovery events (`CASO_RECUPERACAO_ESCALADO`, `PROMESSA_PAGAMENTO_REGISTRADA`, related events) through backbone `010`

**Checkpoint**: User Story 2 should support governed human intervention independently

---

## Phase 5: User Story 3 - Medir recuperação e reengajar assinantes em risco (Priority: P3)

**Goal**: Give super admins operational visibility into recovery effectiveness and controlled post-recovery reengagement

**Independent Test**: The platform can aggregate open, escalated and recovered cases and expose a dashboard with filters and recovery outcomes per channel and stage

### Tests for User Story 3 ⚠️

- [x] T026 [P] [US3] Create feature test for revenue recovery dashboard visibility in `tests/Feature/PlatformRevenueRecoveryDashboardTest.php`
- [x] T027 [P] [US3] Create feature test for stage, severity and owner filters in `tests/Feature/PlatformRevenueRecoveryFiltersTest.php`
- [x] T028 [P] [US3] Create unit test for recovery summary aggregation and reengagement eligibility in `tests/Unit/PlatformRevenueRecoverySummaryTest.php`

### Implementation for User Story 3

- [x] T029 [P] [US3] Implement `PlatformRevenueRecoverySummaryService` in `app/Services/Billing/PlatformRevenueRecoverySummaryService.php`
- [x] T030 [US3] Implement super admin Livewire dashboard for recovery health in `app/Livewire/Admin/PlatformRevenueRecoveryDashboard.php`
- [x] T031 [US3] Implement central filters, summaries and drill-down actions in dashboard views under `resources/views/livewire/admin/`
- [x] T032 [US3] Expose central recovery inspection endpoints or reusable query services for operational support in `app/Http/Controllers/` or `app/Services/Billing/`

**Checkpoint**: User Story 3 should provide operational recovery visibility independently

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T033 [P] Document recovery rollback, promise handling, replay rules and escalation procedures in `GO_LIVE_RUNBOOK.md` and related operational docs
- [x] T034 Update architecture and product documentation for the new recovery layer in `ARCHITECTURE.md`, `README.md` and roadmap artifacts when implementation starts
- [x] T035 [P] Add targeted coverage for recovery event publication, promise serialization and replay audit trail in `tests/Feature/` and `tests/Unit/`
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

- **US1 (P1)**: Starts after Foundational and establishes central revenue recovery MVP
- **US2 (P2)**: Starts after Foundational and depends logically on active recovery cases from US1
- **US3 (P3)**: Starts after Foundational and can overlap late US2 work once summaries and events exist

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
3. Deliver US1 with case opening, scheduling and deduplication
4. Validate automated recovery entry before adding escalation and reengagement

### Incremental Delivery

1. Add US1 for automated case opening and first-stage actions
2. Add US2 for escalation, promises and governed human follow-up
3. Add US3 for central visibility and reengagement readiness
4. Finish with documentation, audit refinement and quickstart validation

## Notes

- Tests must fail before implementation begins
- No task should move recovery workflow state into tenant databases
- Regularização financeira do `012` deve prevalecer sobre ações de cobrança ainda pendentes
- Backup/restore and rollback evidence must be updated when central recovery state is introduced
