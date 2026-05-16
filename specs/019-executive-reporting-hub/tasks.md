# Tasks: Módulo 019 - Executive Reporting Hub

**Input**: Design documents from `/specs/019-executive-reporting-hub/`  
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

**Purpose**: Prepare executive reporting namespace and planning references

- [x] T001 Create executive reporting service namespace scaffolding in `app/Services/Billing/` and related central test baselines in `tests/`
- [x] T002 [P] Register feature documentation references and plan pointer consistency in `AGENTS.md`, `ROADMAP.md` and `specs/019-executive-reporting-hub/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core central infrastructure required before user story work

**⚠️ CRITICAL**: No user story work should begin until this phase is complete

- [x] T003 Create central migrations for `executive_analytics_snapshots`, `executive_report_definitions`, `executive_report_exports` and `executive_report_execution_logs` in `database/migrations/central/`
- [x] T004 [P] Create Eloquent models for executive snapshots, report definitions, exports and execution logs in `app/Models/`
- [x] T005 [P] Create shared enums/value objects for report format, execution status and snapshot lifecycle in `app/Support/`
- [x] T006 Create authorization policy/gate baseline for executive reporting operations in `app/Policies/` and `app/Providers/AppServiceProvider.php`
- [x] T007 Create configuration entries for supported report sections, export guardrails and fallback reporting behavior in `config/`
- [x] T008 Create foundational reporting event publication hooks in `app/Services/Billing/`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Explorar dashboard executivo expandido (Priority: P1) 🎯 MVP

**Goal**: Provide a richer super admin executive dashboard with reusable filters and drill-down

**Independent Test**: A super admin can apply filters and inspect executive KPIs plus drill-down without exporting anything

### Tests for User Story 1 ⚠️

- [x] T009 [P] [US1] Create feature test for executive dashboard summary and filter behavior in `tests/Feature/ExecutiveReportingDashboardTest.php`
- [x] T010 [P] [US1] Create feature test for executive drill-down visibility and access control in `tests/Feature/ExecutiveReportingDrilldownTest.php`
- [x] T011 [P] [US1] Create unit test for executive filter normalization and KPI consistency rules in `tests/Unit/ExecutiveReportingFilterRulesTest.php`

### Implementation for User Story 1

- [x] T012 [P] [US1] Implement executive analytics snapshot aggregation service in `app/Services/Billing/`
- [x] T013 [P] [US1] Implement executive drill-down query service in `app/Services/Billing/`
- [x] T014 [US1] Implement super admin executive reporting dashboard in `app/Livewire/Admin/`
- [x] T015 [US1] Implement dashboard Blade view with executive filters, KPI cards and drill-down areas in `resources/views/livewire/admin/`
- [x] T016 [US1] Expose reusable executive snapshot queries for dashboard and inspection layers

**Checkpoint**: User Story 1 should provide executive exploration independently

---

## Phase 4: User Story 2 - Gerar relatórios executivos exportáveis (Priority: P2)

**Goal**: Generate coherent Excel and PDF reports from the same executive snapshot

**Independent Test**: An operator can export one executive recorte in both formats with consistent indicators and audit trail

### Tests for User Story 2 ⚠️

- [x] T017 [P] [US2] Create feature test for executive Excel export generation in `tests/Feature/ExecutiveReportingExcelExportTest.php`
- [x] T018 [P] [US2] Create feature test for executive PDF export generation in `tests/Feature/ExecutiveReportingPdfExportTest.php`
- [x] T019 [P] [US2] Create unit test for report export consistency and format rules in `tests/Unit/ExecutiveReportingExportRulesTest.php`

### Implementation for User Story 2

- [x] T020 [P] [US2] Implement executive report definition and export orchestration service in `app/Services/Billing/`
- [x] T021 [P] [US2] Implement report artifact projection services for Excel and PDF in `app/Services/Billing/`
- [x] T022 [US2] Implement dashboard export controls and status feedback in `app/Livewire/Admin/` and `resources/views/livewire/admin/`
- [x] T023 [US2] Implement requests/validation for export and inspection queries in `app/Http/Requests/`
- [x] T024 [US2] Publish executive reporting generation events through backbone `010`

**Checkpoint**: User Story 2 should support governed executive report generation independently

---

## Phase 5: User Story 3 - Auditar, reexecutar e inspecionar relatórios gerados (Priority: P3)

**Goal**: Preserve executive reporting history with auditable reexecution and inspection

**Independent Test**: An operator can inspect prior exports, reexecute a report and confirm the new execution against the saved context

### Tests for User Story 3 ⚠️

- [x] T025 [P] [US3] Create feature test for executive reporting history and inspection filters in `tests/Feature/ExecutiveReportingInspectionTest.php`
- [x] T026 [P] [US3] Create feature test for report reexecution audit trail in `tests/Feature/ExecutiveReportingReexecutionTest.php`
- [x] T027 [P] [US3] Create unit test for execution log lifecycle and reexecution rules in `tests/Unit/ExecutiveReportingExecutionRulesTest.php`

### Implementation for User Story 3

- [x] T028 [P] [US3] Implement executive reporting inspection service in `app/Services/Billing/`
- [x] T029 [P] [US3] Implement report reexecution and execution log recording service in `app/Services/Billing/`
- [x] T030 [US3] Implement inspection endpoint/controller or reusable query service in `app/Http/Controllers/` or `app/Services/`
- [x] T031 [US3] Implement reporting history and reexecution exposure in dashboard/admin operations in `app/Livewire/Admin/`
- [x] T032 [US3] Expose central report history, reexecution context and audit log inspection for governance support

**Checkpoint**: User Story 3 should make executive reporting governance auditable independently

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T033 [P] Document executive reporting smoke, export and reexecution procedures in `GO_LIVE_RUNBOOK.md` and related docs
- [x] T034 Update architecture and product documentation for the executive reporting layer in `ARCHITECTURE.md`, `README.md` and roadmap artifacts when implementation starts
- [x] T035 [P] Add targeted coverage for reporting event publication, snapshot serialization and export audit trail in `tests/Feature/` and `tests/Unit/`
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

- **US1 (P1)**: Starts after Foundational and establishes executive snapshot exploration
- **US2 (P2)**: Starts after Foundational and depends logically on executive snapshot visibility from US1
- **US3 (P3)**: Starts after Foundational and can overlap late US2 work once exports and logs exist

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
3. Deliver US1 with executive dashboard and drill-down
4. Validate consistency against existing analytics constraints before expanding exports

### Incremental Delivery

1. Add US1 for executive dashboard exploration
2. Add US2 for governed Excel/PDF exports
3. Add US3 for audit trail, reexecution and inspection
4. Finish with documentation, event refinement and quickstart validation

## Notes

- Tests must fail before implementation begins
- No task should duplicate the source of truth already established in module `014`
- Export and snapshot consistency rules must remain explicit and reusable
- Reexecution and audit evidence must be updated when report generation is introduced
