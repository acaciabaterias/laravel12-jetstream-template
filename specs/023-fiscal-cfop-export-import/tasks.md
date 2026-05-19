# Tasks: Módulo 023 - Fiscal CFOP Export/Import

**Input**: Design documents from `/specs/023-fiscal-cfop-export-import/`  
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

**Purpose**: Prepare fiscal governance module namespace and planning references

- [x] T001 Create fiscal governance service namespace scaffolding in `app/Services/Fiscal/` and related central test baselines in `tests/`
- [x] T002 [P] Register feature documentation references and plan pointer consistency in `AGENTS.md`, `ROADMAP.md` and `specs/023-fiscal-cfop-export-import/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core central infrastructure required before user story work

**⚠️ CRITICAL**: No user story work should begin until this phase is complete

- [x] T003 Create central migrations for CFOP catalog, fiscal scenarios, rule publication and issue governance in `database/migrations/central/`
- [x] T004 [P] Create Eloquent models for fiscal publication, scenario mappings and issue reports in `app/Models/`
- [x] T005 [P] Create shared enums/value objects for publication status, issue severity and resolution status in `app/Support/Fiscal/`
- [x] T006 Create authorization policy/gate baseline for platform fiscal governance in `app/Policies/` and `app/Providers/AppServiceProvider.php`
- [x] T007 Create configuration entries for required scenarios, fallback rules and supported fiscal directions in `config/platform_fiscal_rules.php`
- [x] T008 Create foundational event publication hooks for fiscal publication and rollback transitions in `app/Services/Fiscal/`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Consultar enquadramento fiscal por cenário de exportação/importação (Priority: P1) 🎯 MVP

**Goal**: Resolve fiscal classification per required scenario with safe fallback for central administrative consultation

**Independent Test**: A fiscal operator can query an export or import scenario and receive the active CFOP suggestion or the governed fallback if the scenario is unavailable

### Tests for User Story 1 ⚠️

- [x] T009 [P] [US1] Create feature test for fiscal scenario consultation in `tests/Feature/PlatformFiscalScenarioLookupTest.php`
- [x] T010 [P] [US1] Create feature test for scenario fallback resolution in `tests/Feature/PlatformFiscalFallbackTest.php`
- [x] T011 [P] [US1] Create unit test for fiscal resolution rules in `tests/Unit/PlatformFiscalResolutionRulesTest.php`

### Implementation for User Story 1

- [x] T012 [P] [US1] Implement fiscal scenario resolution and fallback services in `app/Services/Fiscal/`
- [x] T013 [US1] Implement central consultation workflow for fiscal scenarios in `app/Livewire/Admin/` and `resources/views/livewire/admin/`
- [x] T014 [US1] Expose query endpoint or inspection-ready scenario lookup in `app/Http/Controllers/Admin/`
- [x] T015 [US1] Add central scenario summary rendering to the admin fiscal panel in `resources/views/`

**Checkpoint**: User Story 1 should provide fiscal scenario lookup independently

---

## Phase 4: User Story 2 - Publicar catálogo governado de CFOPs e regras fiscais (Priority: P2)

**Goal**: Publish governed fiscal bundles with active CFOP catalog and measurable scenario coverage

**Independent Test**: A fiscal analyst can publish CFOPs and scenario mappings, inspect coverage and record rule issues without breaking the current healthy publication

### Tests for User Story 2 ⚠️

- [x] T016 [P] [US2] Create feature test for fiscal publication workflow in `tests/Feature/PlatformFiscalPublicationTest.php`
- [x] T017 [P] [US2] Create feature test for coverage snapshot and issue report generation in `tests/Feature/PlatformFiscalCoverageTest.php`
- [x] T018 [P] [US2] Create unit test for publication guardrails and scenario rules in `tests/Unit/PlatformFiscalPublicationRulesTest.php`

### Implementation for User Story 2

- [x] T019 [P] [US2] Implement scenario coverage and fiscal issue detection services in `app/Services/Fiscal/`
- [x] T020 [P] [US2] Implement fiscal publication/versioning service in `app/Services/Fiscal/`
- [x] T021 [US2] Implement administrative manager workflow for publication and scenario review in `app/Livewire/Admin/`
- [x] T022 [US2] Implement requests/validation for fiscal publication and required scenario configuration in `app/Http/Requests/Admin/`
- [x] T023 [US2] Publish platform fiscal governance events through backbone `010`

**Checkpoint**: User Story 2 should support governed fiscal rollout independently

---

## Phase 5: User Story 3 - Inspecionar regras fiscais e reverter publicação degradada (Priority: P3)

**Goal**: Provide central visibility into fiscal gaps and allow governed rollback to the last healthy publication

**Independent Test**: A super admin can inspect active fiscal coverage and execute rollback when a publication is degraded or inconsistent

### Tests for User Story 3 ⚠️

- [x] T024 [P] [US3] Create feature test for fiscal inspection filters in `tests/Feature/PlatformFiscalInspectionTest.php`
- [x] T025 [P] [US3] Create feature test for governed fiscal publication rollback in `tests/Feature/PlatformFiscalRollbackTest.php`
- [x] T026 [P] [US3] Create unit test for rollback eligibility and issue severity rules in `tests/Unit/PlatformFiscalRollbackRulesTest.php`

### Implementation for User Story 3

- [x] T027 [P] [US3] Implement fiscal inspection summary service in `app/Services/Fiscal/`
- [x] T028 [P] [US3] Implement fiscal publication rollback service in `app/Services/Fiscal/`
- [x] T029 [US3] Implement super admin Livewire dashboard and inspection endpoint for fiscal governance in `app/Livewire/Admin/` and `app/Http/Controllers/Admin/`
- [x] T030 [US3] Implement rollback controls, issue drill-down and summary views in `resources/views/livewire/admin/`
- [x] T031 [US3] Expose central publication history and issue evidence for governance support

**Checkpoint**: User Story 3 should make fiscal governance auditable independently

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T032 [P] Document fiscal publication, fallback response and rollback procedures in `GO_LIVE_RUNBOOK.md` and related docs
- [x] T033 Update architecture and product documentation for the fiscal governance layer in `ARCHITECTURE.md`, `README.md` and roadmap artifacts when implementation starts
- [x] T034 [P] Add targeted coverage for fiscal event publication, scenario lookup and rollback audit trail in `tests/Feature/` and `tests/Unit/`
- [x] T035 Perform code cleanup and Laravel Pint on changed files
- [x] T036 Run `quickstart.md` validation and record evidence in feature artifacts
