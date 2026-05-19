# Tasks: Módulo 022 - Multi-Currency Support

**Input**: Design documents from `/specs/022-multi-currency-support/`  
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

**Purpose**: Prepare multi-currency module namespace and planning references

- [x] T001 Create currency service namespace scaffolding in `app/Services/Platform/` and related central test baselines in `tests/`
- [x] T002 [P] Register feature documentation references and plan pointer consistency in `AGENTS.md`, `ROADMAP.md` and `specs/022-multi-currency-support/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core central infrastructure required before user story work

**⚠️ CRITICAL**: No user story work should begin until this phase is complete

- [x] T003 Create central migrations for currency preferences, catalog, exchange-rate publication and issue governance in `database/migrations/central/`
- [x] T004 [P] Create Eloquent models for currency publication, rate entries and issue reports in `app/Models/`
- [x] T005 [P] Create shared enums/value objects for publication status, issue severity and resolution status in `app/Support/Platform/`
- [x] T006 Create authorization policy/gate baseline for platform currencies in `app/Policies/` and `app/Providers/AppServiceProvider.php`
- [x] T007 Create configuration entries for supported currencies, default/base currency rules and mandatory conversion coverage in `config/platform_currencies.php`
- [x] T008 Create foundational event publication hooks for currency publication and rollback transitions in `app/Services/Platform/`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Exibir valores centrais na moeda preferida do operador (Priority: P1) 🎯 MVP

**Goal**: Resolve and persist display currency per platform operator with safe fallback on each administrative request

**Independent Test**: A platform operator can change preferred currency to `USD` or `EUR` and see central values rendered in that currency or the active default if unavailable

### Tests for User Story 1 ⚠️

- [x] T009 [P] [US1] Create feature test for operator currency preference switching in `tests/Feature/PlatformCurrencyPreferenceTest.php`
- [x] T010 [P] [US1] Create feature test for request currency fallback resolution in `tests/Feature/PlatformCurrencyFallbackTest.php`
- [x] T011 [P] [US1] Create unit test for currency resolution rules in `tests/Unit/PlatformCurrencyResolutionRulesTest.php`

### Implementation for User Story 1

- [x] T012 [P] [US1] Implement currency resolution, conversion and preference services in `app/Services/Platform/`
- [x] T013 [US1] Persist operator preferred currency in `app/Models/UsuarioPlataforma.php` and related migration/factory updates
- [x] T014 [US1] Apply central currency formatting to key admin dashboard/billing views in `resources/views/`
- [x] T015 [US1] Expose operator currency preference controls in `app/Livewire/Admin/` and `resources/views/livewire/admin/`

**Checkpoint**: User Story 1 should provide operator-level currency projection independently

---

## Phase 4: User Story 2 - Publicar moedas suportadas e tabela de câmbio ativa (Priority: P2)

**Goal**: Publish governed currency bundles with active base/default currency and measurable conversion coverage

**Independent Test**: A financial operator can publish supported currencies and exchange rates, inspect conversion coverage and record inconsistent rates without breaking the current healthy publication

### Tests for User Story 2 ⚠️

- [x] T016 [P] [US2] Create feature test for currency publication workflow in `tests/Feature/PlatformCurrencyPublicationTest.php`
- [x] T017 [P] [US2] Create feature test for rate snapshot and issue report generation in `tests/Feature/PlatformCurrencyCoverageTest.php`
- [x] T018 [P] [US2] Create unit test for publication guardrails and conversion rules in `tests/Unit/PlatformCurrencyPublicationRulesTest.php`

### Implementation for User Story 2

- [x] T019 [P] [US2] Implement currency coverage and conversion issue detection services in `app/Services/Platform/`
- [x] T020 [P] [US2] Implement currency publication/versioning service in `app/Services/Platform/`
- [x] T021 [US2] Implement administrative manager workflow for publication and preference updates in `app/Livewire/Admin/`
- [x] T022 [US2] Implement requests/validation for currency publication and active default/base configuration in `app/Http/Requests/Admin/`
- [x] T023 [US2] Publish platform currency events through backbone `010`

**Checkpoint**: User Story 2 should support governed multi-currency rollout independently

---

## Phase 5: User Story 3 - Inspecionar conversões e reverter tabela degradada (Priority: P3)

**Goal**: Provide central visibility into conversion gaps and allow governed rollback to the last healthy publication

**Independent Test**: A super admin can inspect active currency coverage and execute rollback when a publication is degraded or inconsistent

### Tests for User Story 3 ⚠️

- [x] T024 [P] [US3] Create feature test for currency inspection filters in `tests/Feature/PlatformCurrencyInspectionTest.php`
- [x] T025 [P] [US3] Create feature test for governed currency publication rollback in `tests/Feature/PlatformCurrencyRollbackTest.php`
- [x] T026 [P] [US3] Create unit test for rollback eligibility and issue severity rules in `tests/Unit/PlatformCurrencyRollbackRulesTest.php`

### Implementation for User Story 3

- [x] T027 [P] [US3] Implement currency inspection summary service in `app/Services/Platform/`
- [x] T028 [P] [US3] Implement currency publication rollback service in `app/Services/Platform/`
- [x] T029 [US3] Implement super admin Livewire dashboard and inspection endpoint for currency governance in `app/Livewire/Admin/` and `app/Http/Controllers/Admin/`
- [x] T030 [US3] Implement rollback controls, issue drill-down and summary views in `resources/views/livewire/admin/`
- [x] T031 [US3] Expose central publication history and issue evidence for governance support

**Checkpoint**: User Story 3 should make multi-currency governance auditable independently

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T032 [P] Document currency publication, fallback response and rollback procedures in `GO_LIVE_RUNBOOK.md` and related docs
- [x] T033 Update architecture and product documentation for the multi-currency layer in `ARCHITECTURE.md`, `README.md` and roadmap artifacts when implementation starts
- [x] T034 [P] Add targeted coverage for currency event publication, preference persistence and rollback audit trail in `tests/Feature/` and `tests/Unit/`
- [x] T035 Perform code cleanup and Laravel Pint on changed files
- [x] T036 Run `quickstart.md` validation and record evidence in feature artifacts
