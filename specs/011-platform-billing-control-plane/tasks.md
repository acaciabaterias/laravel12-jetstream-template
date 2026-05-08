# Tasks: Módulo 011 - Platform Billing Control Plane

**Input**: Design documents from `/specs/011-platform-billing-control-plane/`
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

**Purpose**: Prepare central billing namespace and feature baseline

- [x] T001 Create billing service namespace scaffolding in `app/Services/Billing/` and contract namespace updates in `app/Services/Contracts/`
- [x] T002 Create feature and unit test namespace baseline for platform billing in `tests/Feature/` and `tests/Unit/`
- [ ] T003 [P] Register feature documentation references and central plan pointer consistency in `AGENTS.md` and `specs/011-platform-billing-control-plane/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core central infrastructure required before any user story implementation

**⚠️ CRITICAL**: No user story work should begin until this phase is complete

- [x] T004 Create central migrations for `planos_comerciais`, `assinaturas_plataforma`, `faturas_saas`, `politicas_inadimplencia` and `eventos_comerciais_assinante` in `database/migrations/central/`
- [x] T005 [P] Create Eloquent models `PlanoComercial`, `AssinaturaPlataforma`, `FaturaSaaS`, `PoliticaInadimplencia` and `EventoComercialAssinante` in `app/Models/`
- [x] T006 [P] Create shared enums/value objects for billing status, invoice status and commercial events in `app/Support/Billing/`
- [x] T007 Create authorization policy/gate baseline for super admin billing operations in `app/Policies/` and `app/Providers/AppServiceProvider.php`
- [x] T008 Create central billing configuration entries in `config/services.php` or dedicated billing config file for grace period, notification defaults and event publication
- [x] T009 Create foundational audit/event publication hooks for commercial state transitions in `app/Services/Billing/`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Gerir assinatura e plano do assinante (Priority: P1) 🎯 MVP

**Goal**: Manage plans and subscriptions centrally with full history and state control

**Independent Test**: A super admin can create a plan, activate a subscription, change plan and cancel it while preserving commercial history

### Tests for User Story 1 ⚠️

- [x] T010 [P] [US1] Create feature test for plan and subscription lifecycle in `tests/Feature/PlatformBillingSubscriptionLifecycleTest.php`
- [x] T011 [P] [US1] Create feature test for plan migration and history preservation in `tests/Feature/PlatformBillingPlanChangeTest.php`
- [x] T012 [P] [US1] Create unit test for subscription state transitions in `tests/Unit/PlatformSubscriptionStateMachineTest.php`

### Implementation for User Story 1

- [x] T013 [P] [US1] Implement `PlanCatalogService` in `app/Services/Billing/PlanCatalogService.php`
- [x] T014 [P] [US1] Implement `SubscriptionLifecycleService` in `app/Services/Billing/SubscriptionLifecycleService.php`
- [ ] T015 [US1] Implement central admin Livewire screens for plans and subscriptions in `app/Livewire/Admin/`
- [ ] T016 [US1] Implement requests/validation for plan creation and subscription activation in `app/Http/Requests/`
- [x] T017 [US1] Persist commercial history and state transitions through `EventoComercialAssinante`

**Checkpoint**: User Story 1 should manage plans and subscriptions independently

---

## Phase 4: User Story 2 - Cobrar e aplicar política de inadimplência (Priority: P2)

**Goal**: Apply grace period, blocking and reactivation based on central billing rules

**Independent Test**: A SaaS invoice can become overdue, trigger grace period, block the subscriber and later reactivate after regularization

### Tests for User Story 2 ⚠️

- [x] T018 [P] [US2] Create feature test for overdue invoice and grace period evaluation in `tests/Feature/PlatformBillingDelinquencyPolicyTest.php`
- [x] T019 [P] [US2] Create feature test for block and reactivation flow in `tests/Feature/PlatformBillingBlockReactivationTest.php`
- [x] T020 [P] [US2] Create unit test for delinquency policy rules in `tests/Unit/DelinquencyPolicyEvaluatorTest.php`

### Implementation for User Story 2

- [x] T021 [P] [US2] Implement `SaasInvoiceService` in `app/Services/Billing/SaasInvoiceService.php`
- [x] T022 [P] [US2] Implement `DelinquencyPolicyEvaluator` in `app/Services/Billing/DelinquencyPolicyEvaluator.php`
- [x] T023 [US2] Implement job/command for recurring delinquency assessment in `app/Jobs/` or `app/Console/Commands/`
- [x] T024 [US2] Integrate commercial block/unblock flow with existing `BillingAccessGuard` and related central checks
- [ ] T025 [US2] Publish central commercial events (`ASSINANTE_BLOQUEADO`, `ASSINANTE_REATIVADO`, related events) through backbone `010`

**Checkpoint**: User Story 2 should apply delinquency policy independently

---

## Phase 5: User Story 3 - Operar a saúde comercial da base (Priority: P3)

**Goal**: Give super admins a consolidated commercial operations panel

**Independent Test**: The platform can filter subscribers by status, overdue exposure, grace period and recent reactivations in a central dashboard

### Tests for User Story 3 ⚠️

- [x] T026 [P] [US3] Create feature test for billing operations dashboard visibility in `tests/Feature/PlatformBillingDashboardTest.php`
- [x] T027 [P] [US3] Create feature test for central filters and overdue portfolio summaries in `tests/Feature/PlatformBillingPortfolioFiltersTest.php`
- [x] T028 [P] [US3] Create unit test for commercial summary aggregation in `tests/Unit/PlatformBillingSummaryAggregatorTest.php`

### Implementation for User Story 3

- [x] T029 [P] [US3] Implement `PlatformBillingSummaryService` in `app/Services/Billing/PlatformBillingSummaryService.php`
- [x] T030 [US3] Implement super admin Livewire dashboard for commercial health in `app/Livewire/Admin/PlatformBillingDashboard.php`
- [x] T031 [US3] Implement central filters, summaries and drill-down actions in dashboard views under `resources/views/livewire/admin/`
- [ ] T032 [US3] Expose central billing inspection endpoints or reusable query services for operational support in `app/Http/Controllers/` or `app/Services/Billing/`

**Checkpoint**: User Story 3 should provide operational commercial visibility independently

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] T033 [P] Document commercial rollback, blocking policy and operational procedures in `GO_LIVE_RUNBOOK.md` and related operational docs
- [ ] T034 Update architecture and product documentation for the new control plane in `ARCHITECTURE.md`, `README.md` and roadmap artifacts when implementation starts
- [ ] T035 [P] Add extra unit coverage for event publication, audit trail formatting and state serialization in `tests/Unit/`
- [ ] T036 Perform code cleanup and Laravel Pint on changed files
- [ ] T037 Run `quickstart.md` validation and record evidence in feature artifacts

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies
- **Foundational (Phase 2)**: Depends on Setup completion and blocks all user stories
- **User Stories (Phase 3-5)**: Depend on Foundational completion
- **Polish (Phase 6)**: Depends on desired user stories being complete

### User Story Dependencies

- **US1 (P1)**: Starts after Foundational and establishes MVP
- **US2 (P2)**: Starts after Foundational and depends logically on subscription/invoice state from US1
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
3. Deliver US1 with plans and subscriptions
4. Validate subscription lifecycle before adding delinquency logic

### Incremental Delivery

1. Add US1 for central commercial contract management
2. Add US2 for delinquency policy and operational block/reactivation
3. Add US3 for super admin visibility and portfolio operations
4. Finish with documentation, audit refinement and quickstart validation

## Notes

- Tests must fail before implementation begins
- No task should move commercial state into tenant databases
- Block and unblock flows require explicit authorization and auditability
- Backup/restore and rollback evidence must be updated when central commercial state is introduced
