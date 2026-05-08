# Tasks: Módulo 014 - Platform Commercial Analytics

**Input**: Design documents from `/specs/014-platform-commercial-analytics/`
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

**Purpose**: Prepare central analytics namespace and feature baseline

- [ ] T001 Create analytics service namespace scaffolding in `app/Services/Billing/` and related contracts updates in `app/Services/Contracts/`
- [ ] T002 Create feature and unit test namespace baseline for platform commercial analytics in `tests/Feature/` and `tests/Unit/`
- [ ] T003 [P] Register feature documentation references and central plan pointer consistency in `AGENTS.md` and `specs/014-platform-commercial-analytics/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core central infrastructure required before any user story implementation

**⚠️ CRITICAL**: No user story work should begin until this phase is complete

- [ ] T004 Create central migrations for `snapshots_analytics_comercial`, `recortes_coorte_comercial`, `metric_channel_performance`, `insights_risco_comercial` and `drilldowns_analytics_comercial` in `database/migrations/central/`
- [ ] T005 [P] Create Eloquent models for commercial analytics snapshots, cohorts, channel metrics, risk insights and drill-down references in `app/Models/`
- [ ] T006 [P] Create shared enums/value objects for snapshot type, risk category, channel dimension and rebuild status in `app/Support/Billing/`
- [ ] T007 Create authorization policy/gate baseline for super admin and commercial analytics operations in `app/Policies/` and `app/Providers/AppServiceProvider.php`
- [ ] T008 Create central analytics configuration entries for rebuild windows, default periods and segmentation limits in `config/services.php` or dedicated analytics config file
- [ ] T009 Create foundational event publication hooks for executive snapshots and insight transitions in `app/Services/Billing/`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Consolidar métricas executivas do SaaS (Priority: P1) 🎯 MVP

**Goal**: Produce central executive snapshots for MRR, churn, delinquency and recovery

**Independent Test**: A super admin can calculate and inspect one executive snapshot summarizing current central commercial health without manual exports

### Tests for User Story 1 ⚠️

- [ ] T010 [P] [US1] Create feature test for executive snapshot generation from central billing/payment/recovery data in `tests/Feature/PlatformCommercialAnalyticsSnapshotTest.php`
- [ ] T011 [P] [US1] Create feature test for dashboard summary visibility in `tests/Feature/PlatformCommercialAnalyticsDashboardTest.php`
- [ ] T012 [P] [US1] Create unit test for MRR/churn/recovery aggregation rules in `tests/Unit/PlatformCommercialAnalyticsRulesTest.php`

### Implementation for User Story 1

- [ ] T013 [P] [US1] Implement `CommercialAnalyticsSnapshotService` in `app/Services/Billing/CommercialAnalyticsSnapshotService.php`
- [ ] T014 [P] [US1] Implement rebuild command/job for executive snapshots in `app/Console/Commands/` and `app/Jobs/`
- [ ] T015 [US1] Implement executive summary dashboard in `app/Livewire/Admin/`
- [ ] T016 [US1] Persist executive snapshot history and publish snapshot update events through backbone `010`
- [ ] T017 [US1] Expose reusable summary queries for dashboard and inspection layers

**Checkpoint**: User Story 1 should provide executive platform visibility independently

---

## Phase 4: User Story 2 - Analisar coortes, canais e carteiras (Priority: P2)

**Goal**: Segment executive performance by cohort, channel and portfolio

**Independent Test**: A platform manager can filter analytics by cohort or channel and compare retention, churn and recovery across segments

### Tests for User Story 2 ⚠️

- [ ] T018 [P] [US2] Create feature test for cohort segmentation in `tests/Feature/PlatformCommercialAnalyticsCohortTest.php`
- [ ] T019 [P] [US2] Create feature test for channel performance segmentation in `tests/Feature/PlatformCommercialAnalyticsChannelTest.php`
- [ ] T020 [P] [US2] Create unit test for cohort and channel grouping logic in `tests/Unit/PlatformCommercialAnalyticsSegmentationTest.php`

### Implementation for User Story 2

- [ ] T021 [P] [US2] Implement `CommercialAnalyticsCohortService` in `app/Services/Billing/CommercialAnalyticsCohortService.php`
- [ ] T022 [P] [US2] Implement `CommercialAnalyticsChannelService` in `app/Services/Billing/CommercialAnalyticsChannelService.php`
- [ ] T023 [US2] Implement dashboard filters and segmented views in `app/Livewire/Admin/` and `resources/views/livewire/admin/`
- [ ] T024 [US2] Implement requests/validation for segmented inspection queries in `app/Http/Requests/`
- [ ] T025 [US2] Publish central analytical insight events (`COORTE_COMERCIAL_ATUALIZADA`, `CANAL_PERFORMANCE_DEGRADADO`, related events) through backbone `010`

**Checkpoint**: User Story 2 should support comparative analytics independently

---

## Phase 5: User Story 3 - Explorar drill-down e apoiar decisão comercial (Priority: P3)

**Goal**: Link aggregate metrics to the operational records that compose them

**Independent Test**: Leadership can open a metric drill-down and inspect the subscriptions, invoices or recovery cases behind that recut

### Tests for User Story 3 ⚠️

- [ ] T026 [P] [US3] Create feature test for analytics inspection drill-down in `tests/Feature/PlatformCommercialAnalyticsDrilldownTest.php`
- [ ] T027 [P] [US3] Create feature test for risk insight exposure and filtering in `tests/Feature/PlatformCommercialAnalyticsRiskInsightTest.php`
- [ ] T028 [P] [US3] Create unit test for drill-down composition and risk flagging in `tests/Unit/PlatformCommercialAnalyticsDrilldownRulesTest.php`

### Implementation for User Story 3

- [ ] T029 [P] [US3] Implement `CommercialAnalyticsDrilldownService` in `app/Services/Billing/CommercialAnalyticsDrilldownService.php`
- [ ] T030 [US3] Implement analytics inspection endpoint/controller or reusable query service in `app/Http/Controllers/` or `app/Services/Billing/`
- [ ] T031 [US3] Implement risk insight generation and dashboard exposure in `app/Services/Billing/` and `app/Livewire/Admin/`
- [ ] T032 [US3] Expose central drill-down and risk inspection for operational support

**Checkpoint**: User Story 3 should make analytics actionable independently

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] T033 [P] Document snapshot rebuild, rollback, drill-down validation and executive interpretation procedures in `GO_LIVE_RUNBOOK.md` and related docs
- [ ] T034 Update architecture and product documentation for the analytics layer in `ARCHITECTURE.md`, `README.md` and roadmap artifacts when implementation starts
- [ ] T035 [P] Add targeted coverage for analytics event publication, snapshot rebuild audit trail and drill-down serialization in `tests/Feature/` and `tests/Unit/`
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

- **US1 (P1)**: Starts after Foundational and establishes executive commercial visibility
- **US2 (P2)**: Starts after Foundational and depends logically on snapshots from US1
- **US3 (P3)**: Starts after Foundational and can overlap late US2 work once segmented recuts exist

### Parallel Opportunities

- T005, T006 and T007 can run in parallel after migrations are planned
- All tests inside each user story marked `[P]` can run in parallel
- T013 and T014 can run in parallel before T015
- T021 and T022 can run in parallel before T023 and T024
- T029 and T031 can run in parallel before T030 and T032

## Implementation Strategy

### MVP First (US1 only)

1. Complete Setup
2. Complete Foundational phase
3. Deliver US1 with executive snapshot generation and dashboard
4. Validate consistency against the current central modules before segmentation

### Incremental Delivery

1. Add US1 for executive summary and rebuildable snapshots
2. Add US2 for cohort, channel and portfolio comparisons
3. Add US3 for drill-down and risk insights
4. Finish with documentation, rebuild audit refinement and quickstart validation

## Notes

- Tests must fail before implementation begins
- No task should transform analytics into the canonical operational source
- Rebuild must stay conservative and auditable
- Backup/restore and rollback evidence must be updated when central analytics state is introduced
