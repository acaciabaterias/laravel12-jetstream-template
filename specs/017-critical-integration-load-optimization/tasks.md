# Tasks: Módulo 017 - Critical Integration Load Optimization

**Input**: Design documents from `/specs/017-critical-integration-load-optimization/`  
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

**Purpose**: Prepare benchmark and tuning optimization feature namespace and artifacts

- [x] T001 Create load optimization service namespace scaffolding in `app/Services/Operations/` and related central test baselines in `tests/`
- [x] T002 [P] Register feature documentation references and plan pointer consistency in `AGENTS.md`, `ROADMAP.md` and `specs/017-critical-integration-load-optimization/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core central infrastructure required before user story work

**⚠️ CRITICAL**: No user story work should begin until this phase is complete

- [x] T003 Create central migrations for `load_scenario_profiles`, `benchmark_execution_records`, `performance_bottleneck_records`, `tuning_change_records` and `performance_rollback_evidences` in `database/migrations/central/`
- [x] T004 [P] Create Eloquent models for scenario profiles, benchmark executions, bottlenecks, tuning changes and rollback evidences in `app/Models/`
- [x] T005 [P] Create shared enums/value objects for benchmark status, comparison status, bottleneck category and tuning lifecycle in `app/Support/`
- [x] T006 Create authorization policy/gate baseline for critical load optimization in `app/Policies/` and `app/Providers/AppServiceProvider.php`
- [x] T007 Create configuration entries for benchmark tolerances, regression thresholds and rollback guidance in `config/`
- [x] T008 Create foundational benchmark event publication hooks in `app/Services/Operations/`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Medir carga reproduzível dos fluxos críticos (Priority: P1) 🎯 MVP

**Goal**: Register reproducible load scenarios and benchmark executions for critical flows

**Independent Test**: An operator can record a benchmark for one critical flow and compare it against the current baseline without external manual spreadsheets

### Tests for User Story 1 ⚠️

- [x] T009 [P] [US1] Create feature test for load scenario persistence and benchmark recording in `tests/Feature/CriticalLoadBenchmarkRecordingTest.php`
- [x] T010 [P] [US1] Create feature test for benchmark dashboard visibility and access control in `tests/Feature/CriticalLoadOptimizationDashboardTest.php`
- [x] T011 [P] [US1] Create unit test for benchmark comparison tolerance rules in `tests/Unit/CriticalLoadComparisonRulesTest.php`

### Implementation for User Story 1

- [x] T012 [P] [US1] Implement load scenario and benchmark comparison services in `app/Services/Operations/`
- [x] T013 [P] [US1] Implement benchmark recording workflow in `app/Services/Operations/`
- [x] T014 [US1] Implement benchmark dashboard in `app/Livewire/Admin/`
- [x] T015 [US1] Persist benchmark executions and publish material capacity events through backbone `010`
- [x] T016 [US1] Expose reusable benchmark queries for dashboard and inspection layers

**Checkpoint**: User Story 1 should provide reproducible benchmark governance independently

---

## Phase 4: User Story 2 - Encontrar gargalos de query e throughput com governança (Priority: P2)

**Goal**: Register and inspect bottlenecks tied to benchmark executions and critical flows

**Independent Test**: A technical operator can inspect a degraded benchmark and identify the dominant bottleneck category and affected component

### Tests for User Story 2 ⚠️

- [x] T017 [P] [US2] Create feature test for bottleneck persistence and inspection by category in `tests/Feature/CriticalLoadBottleneckInspectionTest.php`
- [x] T018 [P] [US2] Create feature test for benchmark inspection filters by flow and comparison status in `tests/Feature/CriticalLoadInspectionFilterTest.php`
- [x] T019 [P] [US2] Create unit test for bottleneck severity and regression mapping in `tests/Unit/CriticalLoadBottleneckRulesTest.php`

### Implementation for User Story 2

- [x] T020 [P] [US2] Implement bottleneck analysis service in `app/Services/Operations/`
- [x] T021 [P] [US2] Implement critical load inspection service in `app/Services/Operations/`
- [x] T022 [US2] Implement dashboard filters and bottleneck inspection views in `app/Livewire/Admin/` and `resources/views/livewire/admin/`
- [x] T023 [US2] Implement requests/validation for critical load inspection queries in `app/Http/Requests/`
- [x] T024 [US2] Publish bottleneck and regression events through backbone `010`

**Checkpoint**: User Story 2 should support bottleneck governance independently

---

## Phase 5: User Story 3 - Validar tuning e rollback de performance com evidência (Priority: P3)

**Goal**: Track tuning candidates, validation runs and rollback evidence with auditability

**Independent Test**: An operator can register a tuning change, validate its benchmark result and record rollback when the change regresses capacity

### Tests for User Story 3 ⚠️

- [x] T025 [P] [US3] Create feature test for tuning validation inspection in `tests/Feature/CriticalLoadTuningInspectionTest.php`
- [x] T026 [P] [US3] Create feature test for performance rollback evidence recording in `tests/Feature/CriticalLoadRollbackEvidenceTest.php`
- [x] T027 [P] [US3] Create unit test for tuning promotion and rollback lifecycle rules in `tests/Unit/CriticalLoadTuningRulesTest.php`

### Implementation for User Story 3

- [x] T028 [P] [US3] Implement tuning lifecycle service in `app/Services/Operations/`
- [x] T029 [P] [US3] Implement rollback evidence service in `app/Services/Operations/`
- [x] T030 [US3] Implement inspection endpoint/controller or reusable query service in `app/Http/Controllers/` or `app/Services/`
- [x] T031 [US3] Implement tuning and rollback exposure in dashboard/admin operations in `app/Livewire/Admin/`
- [x] T032 [US3] Expose central tuning validation and rollback inspection for operational support

**Checkpoint**: User Story 3 should make tuning governance auditable independently

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T033 [P] Document benchmark stack, bottleneck analysis, tuning validation and rollback procedures in `GO_LIVE_RUNBOOK.md` and related docs
- [x] T034 Update architecture and product documentation for the critical load optimization layer in `ARCHITECTURE.md`, `README.md` and roadmap artifacts when implementation starts
- [x] T035 [P] Add targeted coverage for benchmark event publication, tuning evidence serialization and rollback audit trail in `tests/Feature/` and `tests/Unit/`
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

- **US1 (P1)**: Starts after Foundational and establishes reproducible benchmark governance
- **US2 (P2)**: Starts after Foundational and depends logically on benchmark visibility from US1
- **US3 (P3)**: Starts after Foundational and can overlap late US2 work once benchmark and bottlenecks exist

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
3. Deliver US1 with reproducible benchmark governance
4. Validate consistency against modules `015` and `016` before expanding tuning lifecycle

### Incremental Delivery

1. Add US1 for scenario and benchmark recording
2. Add US2 for bottleneck governance and inspection
3. Add US3 for tuning validation, promotion and rollback evidence
4. Finish with documentation, audit refinement and quickstart validation

## Notes

- Tests must fail before implementation begins
- No task should move performance source of truth fully outside the ERP
- Capacity and regression taxonomies must remain explicit and compatible with modules `015` and `016`
- Backup/restore and rollback evidence must be updated when tuning lifecycle is introduced
