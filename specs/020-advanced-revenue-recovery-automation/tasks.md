# Tasks: Módulo 020 - Advanced Revenue Recovery Automation

**Input**: Design documents from `/specs/020-advanced-revenue-recovery-automation/`  
**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/`

**Tests**: Every feature MUST include explicit test tasks. Tests are mandatory.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this belongs to (`US1`, `US2`, `US3`)
- Include exact file paths in descriptions

## Path Conventions

- Laravel application code in `app/`
- Central migrations in `database/migrations/central/`
- HTTP routes in `routes/`
- Feature tests in `tests/Feature/`
- Unit tests in `tests/Unit/`

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare advanced revenue recovery automation namespace and planning references

- [ ] T001 Create advanced recovery automation service namespace scaffolding in `app/Services/Billing/` and related central test baselines in `tests/`
- [ ] T002 [P] Register feature documentation references and plan pointer consistency in `AGENTS.md`, `ROADMAP.md` and `specs/020-advanced-revenue-recovery-automation/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core central infrastructure required before user story work

**⚠️ CRITICAL**: No user story work should begin until this phase is complete

- [ ] T003 Create central migrations for `recovery_automation_policy_versions`, `recovery_automation_journeys`, `recovery_automation_dispatches`, `recovery_automation_experiments` and `recovery_automation_violations` in `database/migrations/central/`
- [ ] T004 [P] Create Eloquent models for policy versions, journeys, dispatches, experiments and violations in `app/Models/`
- [ ] T005 [P] Create shared enums/value objects for automation policy status, journey status, dispatch status, experiment status and violation severity in `app/Support/Billing/`
- [ ] T006 Create authorization policy/gate baseline for advanced recovery automation operations in `app/Policies/` and `app/Providers/AppServiceProvider.php`
- [ ] T007 Create configuration entries for guardrails, fallback order, cooldown windows and rollback defaults in `config/`
- [ ] T008 Create foundational event publication hooks for automation policy, dispatch and rollback transitions in `app/Services/Billing/`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Orquestrar jornadas automáticas adaptativas (Priority: P1) 🎯 MVP

**Goal**: Advance eligible recovery cases through adaptive automated journeys with fallback, suppression and cooldown awareness

**Independent Test**: A billing operator can evaluate one eligible recovery case and confirm one valid next automated action with the correct fallback behavior and no duplicate dispatch

### Tests for User Story 1 ⚠️

- [ ] T009 [P] [US1] Create feature test for adaptive journey scheduling with valid next action selection in `tests/Feature/AdvancedRecoveryAutomationJourneyTest.php`
- [ ] T010 [P] [US1] Create feature test for channel fallback and suppression-aware dispatch behavior in `tests/Feature/AdvancedRecoveryAutomationFallbackTest.php`
- [ ] T011 [P] [US1] Create unit test for dispatch deduplication and cooldown rules in `tests/Unit/AdvancedRecoveryAutomationDispatchRulesTest.php`

### Implementation for User Story 1

- [ ] T012 [P] [US1] Implement advanced automation policy resolution service in `app/Services/Billing/`
- [ ] T013 [P] [US1] Implement adaptive journey orchestration service in `app/Services/Billing/`
- [ ] T014 [US1] Implement dispatch scheduler/executor with fallback and revalidation in `app/Services/Billing/` and `app/Jobs/`
- [ ] T015 [US1] Persist journey and dispatch history linked to recovery cases and actions from module `013`
- [ ] T016 [US1] Expose reusable automation evaluation queries for dashboard and inspection layers

**Checkpoint**: User Story 1 should advance automated journeys independently

---

## Phase 4: User Story 2 - Publicar estratégias automáticas com governança e experimento (Priority: P2)

**Goal**: Publish versioned automation strategies safely with controlled experiments and holdouts

**Independent Test**: A manager can publish a scoped automation version and confirm that only eligible journeys receive the assigned variant or holdout treatment

### Tests for User Story 2 ⚠️

- [ ] T017 [P] [US2] Create feature test for controlled automation policy publication in `tests/Feature/AdvancedRecoveryAutomationPublicationTest.php`
- [ ] T018 [P] [US2] Create feature test for experiment or holdout allocation persistence in `tests/Feature/AdvancedRecoveryAutomationExperimentTest.php`
- [ ] T019 [P] [US2] Create unit test for publication guardrails and allocation rules in `tests/Unit/AdvancedRecoveryAutomationPolicyRulesTest.php`

### Implementation for User Story 2

- [ ] T020 [P] [US2] Implement automation policy publication/versioning service in `app/Services/Billing/`
- [ ] T021 [P] [US2] Implement experiment and holdout assignment service in `app/Services/Billing/`
- [ ] T022 [US2] Implement manager workflow for draft, approval and scoped publication in `app/Livewire/Admin/`
- [ ] T023 [US2] Implement requests/validation for policy publication, variant definition and holdout configuration in `app/Http/Requests/`
- [ ] T024 [US2] Publish advanced recovery automation publication events through backbone `010`

**Checkpoint**: User Story 2 should support governed strategy rollout independently

---

## Phase 5: User Story 3 - Inspecionar performance e reverter automações degradadas (Priority: P3)

**Goal**: Provide operational visibility into automation performance, violations and governed rollback

**Independent Test**: A super admin can inspect automation performance by version and execute an auditable rollback to the last healthy policy when violations or regressions are detected

### Tests for User Story 3 ⚠️

- [ ] T025 [P] [US3] Create feature test for automation performance and violation inspection in `tests/Feature/AdvancedRecoveryAutomationInspectionTest.php`
- [ ] T026 [P] [US3] Create feature test for governed rollback and affected journey marking in `tests/Feature/AdvancedRecoveryAutomationRollbackTest.php`
- [ ] T027 [P] [US3] Create unit test for violation classification and rollback eligibility in `tests/Unit/AdvancedRecoveryAutomationRollbackRulesTest.php`

### Implementation for User Story 3

- [ ] T028 [P] [US3] Implement automation performance and violation summary service in `app/Services/Billing/`
- [ ] T029 [P] [US3] Implement automation rollback service with journey impact recording in `app/Services/Billing/`
- [ ] T030 [US3] Implement super admin Livewire dashboard and inspection endpoint for automation governance in `app/Livewire/Admin/` and `app/Http/Controllers/`
- [ ] T031 [US3] Implement rollback controls, violation drill-down and summary views in `resources/views/livewire/admin/`
- [ ] T032 [US3] Expose central automation history, violation evidence and rollback context for governance support

**Checkpoint**: User Story 3 should make automation governance auditable independently

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] T033 [P] Document automation publication, violation response and rollback procedures in `GO_LIVE_RUNBOOK.md` and related docs
- [ ] T034 Update architecture and product documentation for the advanced recovery automation layer in `ARCHITECTURE.md`, `README.md` and roadmap artifacts when implementation starts
- [ ] T035 [P] Add targeted coverage for automation event publication, experiment persistence and rollback audit trail in `tests/Feature/` and `tests/Unit/`
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

- **US1 (P1)**: Starts after Foundational and establishes the adaptive automation MVP
- **US2 (P2)**: Starts after Foundational and depends logically on active journeys from US1
- **US3 (P3)**: Starts after Foundational and can overlap late US2 work once summaries and policy versions exist

### Parallel Opportunities

- T004, T005 and T006 can run in parallel after migrations are planned
- All tests inside each user story marked `[P]` can run in parallel
- T012 and T013 can run in parallel before T014
- T020 and T021 can run in parallel before T022 and T023
- T028 and T029 can run in parallel before T030 and T031

## Implementation Strategy

### MVP First (US1 only)

1. Complete Setup
2. Complete Foundational phase
3. Deliver US1 with adaptive journey evaluation and dispatch safety
4. Validate deduplication and fallback correctness before adding experiments and rollback

### Incremental Delivery

1. Add US1 for adaptive automated journeys
2. Add US2 for governed policy publication and experimentation
3. Add US3 for performance inspection and rollback
4. Finish with documentation, audit refinement and quickstart validation

## Notes

- Tests must fail before implementation begins
- No task should duplicate the operational source of truth already introduced in module `013`
- Guardrails and rollback evidence must remain explicit and reusable
- Executive and recovery dashboards should consume the same governed automation history
