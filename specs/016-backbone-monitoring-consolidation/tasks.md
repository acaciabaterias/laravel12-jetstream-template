# Tasks: Módulo 016 - Backbone Monitoring Consolidation

**Input**: Design documents from `/specs/016-backbone-monitoring-consolidation/`  
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

**Purpose**: Prepare monitoring consolidation feature namespace and artifacts

- [x] T001 Create monitoring consolidation service namespace scaffolding in `app/Services/Operations/` and related central test baselines in `tests/`
- [x] T002 [P] Register feature documentation references and plan pointer consistency in `AGENTS.md`, `ROADMAP.md` and `specs/016-backbone-monitoring-consolidation/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core central infrastructure required before user story work

**⚠️ CRITICAL**: No user story work should begin until this phase is complete

- [x] T003 Create central migrations for `monitoring_target_catalogs`, `monitoring_probe_snapshots`, `alert_rule_definitions`, `dashboard_provisioning_records` and `monitoring_readiness_evidences` in `database/migrations/central/`
- [x] T004 [P] Create Eloquent models for monitoring targets, probe snapshots, alert rules, dashboard provisions and readiness evidences in `app/Models/`
- [x] T005 [P] Create shared enums/value objects for scrape health, provisioning status, readiness result and monitoring severity in `app/Support/`
- [x] T006 Create authorization policy/gate baseline for monitoring consolidation in `app/Policies/` and `app/Providers/AppServiceProvider.php`
- [x] T007 Create configuration entries for scrape limits, alert defaults and provisioning windows in `config/`
- [x] T008 Create foundational monitoring event publication hooks in `app/Services/Operations/`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Consolidar coleta e visibilidade externa do backbone (Priority: P1) 🎯 MVP

**Goal**: Register targets, scrape health and readiness visibility for the external monitoring stack

**Independent Test**: An operator can inspect one central dashboard and determine target health, collector status and environment readiness without manual Prometheus digging

### Tests for User Story 1 ⚠️

- [x] T009 [P] [US1] Create feature test for monitoring target readiness and probe snapshots in `tests/Feature/BackboneMonitoringReadinessTest.php`
- [x] T010 [P] [US1] Create feature test for monitoring dashboard visibility and access control in `tests/Feature/BackboneMonitoringDashboardTest.php`
- [x] T011 [P] [US1] Create unit test for scrape health classification rules in `tests/Unit/BackboneMonitoringScrapeRulesTest.php`

### Implementation for User Story 1

- [x] T012 [P] [US1] Implement monitoring target and probe snapshot services in `app/Services/Operations/`
- [x] T013 [P] [US1] Implement readiness rebuild/check command or job in `app/Console/Commands/` and `app/Jobs/`
- [x] T014 [US1] Implement monitoring dashboard in `app/Livewire/Admin/`
- [x] T015 [US1] Persist scrape health snapshots and publish material monitoring events through backbone `010`
- [x] T016 [US1] Expose reusable readiness queries for dashboard and inspection layers

**Checkpoint**: User Story 1 should provide central monitoring readiness independently

---

## Phase 4: User Story 2 - Escalar degradações com regras de alerta verificáveis (Priority: P2)

**Goal**: Version and evaluate alert rules tied to critical flows

**Independent Test**: A support operator can validate which alert rule triggered, for which flow and under which severity threshold

### Tests for User Story 2 ⚠️

- [x] T017 [P] [US2] Create feature test for alert rule persistence and evaluation in `tests/Feature/BackboneMonitoringAlertRulesTest.php`
- [x] T018 [P] [US2] Create feature test for monitoring inspection filters by flow and alert status in `tests/Feature/BackboneMonitoringInspectionFilterTest.php`
- [x] T019 [P] [US2] Create unit test for alert threshold mapping in `tests/Unit/BackboneMonitoringAlertRulesTest.php`

### Implementation for User Story 2

- [x] T020 [P] [US2] Implement alert rule evaluation service in `app/Services/Operations/`
- [x] T021 [P] [US2] Implement monitoring inspection service in `app/Services/Operations/`
- [x] T022 [US2] Implement dashboard filters and alert evaluation views in `app/Livewire/Admin/` and `resources/views/livewire/admin/`
- [x] T023 [US2] Implement requests/validation for monitoring inspection queries in `app/Http/Requests/`
- [x] T024 [US2] Publish alert-materialization events through backbone `010`

**Checkpoint**: User Story 2 should support alert governance independently

---

## Phase 5: User Story 3 - Versionar dashboards e validar readiness de observabilidade (Priority: P3)

**Goal**: Track dashboard packages, provisioning, rollback and readiness evidence with auditability

**Independent Test**: An operator can register a dashboard package, mark it provisioned, roll it back and preserve evidence of validation

### Tests for User Story 3 ⚠️

- [x] T025 [P] [US3] Create feature test for dashboard provisioning inspection in `tests/Feature/BackboneMonitoringProvisioningInspectionTest.php`
- [x] T026 [P] [US3] Create feature test for monitoring rollback evidence recording in `tests/Feature/BackboneMonitoringRollbackEvidenceTest.php`
- [x] T027 [P] [US3] Create unit test for provisioning and rollback lifecycle rules in `tests/Unit/BackboneMonitoringProvisioningRulesTest.php`

### Implementation for User Story 3

- [x] T028 [P] [US3] Implement dashboard provisioning service in `app/Services/Operations/`
- [x] T029 [P] [US3] Implement monitoring readiness evidence service in `app/Services/Operations/`
- [x] T030 [US3] Implement inspection endpoint/controller or reusable query service in `app/Http/Controllers/` or `app/Services/`
- [x] T031 [US3] Implement dashboard package and readiness exposure in dashboard/admin operations in `app/Livewire/Admin/`
- [x] T032 [US3] Expose central provisioning and rollback inspection for operational support

**Checkpoint**: User Story 3 should make monitoring stack governance auditable independently

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T033 [P] Document monitoring stack, scrape validation, dashboard provisioning and rollback procedures in `GO_LIVE_RUNBOOK.md` and related docs
- [x] T034 Update architecture and product documentation for the monitoring consolidation layer in `ARCHITECTURE.md`, `README.md` and roadmap artifacts when implementation starts
- [x] T035 [P] Add targeted coverage for monitoring event publication, provisioning evidence serialization and rollback audit trail in `tests/Feature/` and `tests/Unit/`
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

- **US1 (P1)**: Starts after Foundational and establishes monitoring readiness
- **US2 (P2)**: Starts after Foundational and depends logically on target/probe visibility from US1
- **US3 (P3)**: Starts after Foundational and can overlap late US2 work once readiness and rules exist

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
3. Deliver US1 with central monitoring readiness
4. Validate consistency against backbone `010` and observability `015` before expanding alert and provisioning governance

### Incremental Delivery

1. Add US1 for target readiness and scrape visibility
2. Add US2 for alert rules and inspection governance
3. Add US3 for dashboard versioning, rollback and evidence
4. Finish with documentation, audit refinement and quickstart validation

## Notes

- Tests must fail before implementation begins
- No task should move monitoring source of truth fully outside the ERP
- Alert taxonomies must remain explicit and compatible with modules `010` and `015`
- Backup/restore and rollback evidence must be updated when monitoring provisioning and readiness are introduced
