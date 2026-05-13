# Tasks: Módulo 015 - Production Observability Assurance

**Input**: Design documents from `/specs/015-production-observability-assurance/`  
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

**Purpose**: Prepare observability assurance baseline and feature namespace

- [x] T001 Create observability assurance service namespace scaffolding in `app/Services/` and related central feature baselines in `tests/`
- [x] T002 [P] Register feature documentation references and current plan pointer consistency in `AGENTS.md` and `specs/015-production-observability-assurance/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core central infrastructure required before user story work

**⚠️ CRITICAL**: No user story work should begin until this phase is complete

- [x] T003 Create central migrations for `operational_slo_definitions`, `operational_alert_snapshots`, `load_test_baselines`, `operational_incident_records` and `runbook_execution_evidences` in `database/migrations/central/`
- [x] T004 [P] Create Eloquent models for SLOs, alert snapshots, baselines, incidents and evidences in `app/Models/`
- [x] T005 [P] Create shared enums/value objects for operational severity, incident status, runbook result and collector health in `app/Support/`
- [x] T006 Create authorization policy/gate baseline for operational observability in `app/Policies/` and `app/Providers/AppServiceProvider.php`
- [x] T007 Create configuration entries for SLO thresholds, default windows and load-test tolerances in `config/`
- [x] T008 Create foundational event publication hooks for incidents, degraded services and service recovery in `app/Services/`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Detectar degradação antes de impacto amplo (Priority: P1) 🎯 MVP

**Goal**: Consolidate operational health, classify severity and expose alert-driven visibility

**Independent Test**: An operator can inspect one central dashboard and identify degraded flows, backlog and replay risk without manual log triage

### Tests for User Story 1 ⚠️

- [x] T009 [P] [US1] Create feature test for operational snapshot generation and severity classification in `tests/Feature/ProductionObservabilitySnapshotTest.php`
- [x] T010 [P] [US1] Create feature test for operational dashboard visibility and access control in `tests/Feature/ProductionObservabilityDashboardTest.php`
- [x] T011 [P] [US1] Create unit test for severity classification rules in `tests/Unit/ProductionObservabilitySeverityRulesTest.php`

### Implementation for User Story 1

- [x] T012 [P] [US1] Implement `OperationalHealthSnapshotService` in `app/Services/`
- [x] T013 [P] [US1] Implement snapshot rebuild/check command or job in `app/Console/Commands/` and `app/Jobs/`
- [x] T014 [US1] Implement operational dashboard in `app/Livewire/Admin/`
- [x] T015 [US1] Persist alert snapshots and publish material operational events through backbone `010`
- [x] T016 [US1] Expose reusable summary queries for dashboard and inspection layers

**Checkpoint**: User Story 1 should provide central operational visibility independently

---

## Phase 4: User Story 2 - Validar capacidade e limites de operação (Priority: P2)

**Goal**: Persist and compare load-test baselines for critical flows

**Independent Test**: A technical operator can record a load baseline and identify whether a new execution regressed beyond acceptable thresholds

### Tests for User Story 2 ⚠️

- [x] T017 [P] [US2] Create feature test for load baseline persistence and comparison in `tests/Feature/ProductionObservabilityLoadBaselineTest.php`
- [x] T018 [P] [US2] Create feature test for segmented operational views by flow in `tests/Feature/ProductionObservabilityFlowFilterTest.php`
- [x] T019 [P] [US2] Create unit test for baseline comparison logic in `tests/Unit/ProductionObservabilityBaselineRulesTest.php`

### Implementation for User Story 2

- [x] T020 [P] [US2] Implement `LoadTestBaselineService` in `app/Services/`
- [x] T021 [P] [US2] Implement flow/segment inspection service in `app/Services/`
- [x] T022 [US2] Implement dashboard filters and baseline comparison views in `app/Livewire/Admin/` and `resources/views/livewire/admin/`
- [x] T023 [US2] Implement requests/validation for baseline and inspection queries in `app/Http/Requests/`
- [x] T024 [US2] Publish baseline and degradation events through backbone `010`

**Checkpoint**: User Story 2 should support capacity governance independently

---

## Phase 5: User Story 3 - Executar resposta operacional com evidência auditável (Priority: P3)

**Goal**: Register incidents, runbook execution and post-incident validation with traceability

**Independent Test**: An operator can open an incident, execute a runbook action and close it only after evidence and validation are recorded

### Tests for User Story 3 ⚠️

- [x] T025 [P] [US3] Create feature test for incident inspection and evidence capture in `tests/Feature/ProductionObservabilityIncidentInspectionTest.php`
- [x] T026 [P] [US3] Create feature test for runbook execution recording in `tests/Feature/ProductionObservabilityRunbookEvidenceTest.php`
- [x] T027 [P] [US3] Create unit test for incident lifecycle and closure rules in `tests/Unit/ProductionObservabilityIncidentRulesTest.php`

### Implementation for User Story 3

- [x] T028 [P] [US3] Implement `OperationalIncidentService` in `app/Services/`
- [x] T029 [P] [US3] Implement `RunbookEvidenceService` in `app/Services/`
- [x] T030 [US3] Implement inspection endpoint/controller or reusable query service in `app/Http/Controllers/` or `app/Services/`
- [x] T031 [US3] Implement incident and evidence exposure in dashboard/admin operations in `app/Livewire/Admin/`
- [x] T032 [US3] Expose central incident/runbook inspection for operational support

**Checkpoint**: User Story 3 should make incident response auditable independently

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] T033 [P] Document monitoring, load baseline, incident response and rollback validation procedures in `GO_LIVE_RUNBOOK.md` and related docs
- [ ] T034 Update architecture and product documentation for the operational assurance layer in `ARCHITECTURE.md`, `README.md` and roadmap artifacts when implementation starts
- [ ] T035 [P] Add targeted coverage for operational event publication, runbook evidence serialization and incident audit trail in `tests/Feature/` and `tests/Unit/`
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

- **US1 (P1)**: Starts after Foundational and establishes operational visibility
- **US2 (P2)**: Starts after Foundational and depends logically on operational snapshots from US1
- **US3 (P3)**: Starts after Foundational and can overlap late US2 work once incidents and baselines exist

### Parallel Opportunities

- T004, T005 and T006 can run in parallel after migrations are planned
- All tests inside each user story marked `[P]` can run in parallel
- T012 and T013 can run in parallel before T014
- T020 and T021 can run in parallel before T022 and T023
- T028 and T029 can run in parallel before T030 and T032

## Implementation Strategy

### MVP First (US1 only)

1. Complete Setup
2. Complete Foundational phase
3. Deliver US1 with operational visibility and severity classification
4. Validate consistency against the current central modules before load and incident automation

### Incremental Delivery

1. Add US1 for operational health and alert snapshots
2. Add US2 for load baselines and comparative capacity views
3. Add US3 for incident governance and runbook evidence
4. Finish with documentation, audit refinement and quickstart validation

## Notes

- Tests must fail before implementation begins
- No task should move operational source of truth out of existing modules
- Severity and closure rules must remain explicit and auditable
- Backup/restore and rollback evidence must be updated when operational snapshots and incidents are introduced
