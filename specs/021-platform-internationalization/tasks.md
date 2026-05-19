# Tasks: Módulo 021 - Platform Internationalization

**Input**: Design documents from `/specs/021-platform-internationalization/`  
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

**Purpose**: Prepare localization module namespace and planning references

- [x] T001 Create localization service namespace scaffolding in `app/Services/Platform/` and related central test baselines in `tests/`
- [x] T002 [P] Register feature documentation references and plan pointer consistency in `AGENTS.md`, `ROADMAP.md` and `specs/021-platform-internationalization/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core central infrastructure required before user story work

**⚠️ CRITICAL**: No user story work should begin until this phase is complete

- [x] T003 Create central migrations for locale preference, publication and missing-key governance in `database/migrations/central/`
- [x] T004 [P] Create Eloquent models for locale publication and missing-key reports in `app/Models/`
- [x] T005 [P] Create shared enums/value objects for locale publication status, missing-key severity and resolution status in `app/Support/Platform/`
- [x] T006 Create authorization policy/gate baseline for platform localization in `app/Policies/` and `app/Providers/AppServiceProvider.php`
- [x] T007 Create configuration entries for supported locales, fallback rules and required translation keys in `config/platform_localization.php`
- [x] T008 Create foundational event publication hooks for locale publication and rollback transitions in `app/Services/Platform/`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Alternar idioma da plataforma por operador (Priority: P1) 🎯 MVP

**Goal**: Resolve and persist locale per platform operator with safe fallback on each administrative request

**Independent Test**: A platform operator can change preferred locale to `en` or `es` and see the next admin request rendered in that language or the active fallback if unavailable

### Tests for User Story 1 ⚠️

- [x] T009 [P] [US1] Create feature test for operator locale preference switching in `tests/Feature/PlatformLocalizationPreferenceTest.php`
- [x] T010 [P] [US1] Create feature test for request fallback resolution in `tests/Feature/PlatformLocalizationFallbackTest.php`
- [x] T011 [P] [US1] Create unit test for locale resolution rules in `tests/Unit/PlatformLocaleResolutionRulesTest.php`

### Implementation for User Story 1

- [x] T012 [P] [US1] Implement locale resolution and preference services in `app/Services/Platform/`
- [x] T013 [US1] Implement request middleware for admin locale resolution in `app/Http/Middleware/`
- [x] T014 [US1] Persist operator preferred locale in `app/Models/UsuarioPlataforma.php` and related migration/factory updates
- [x] T015 [US1] Add translated JSON language catalogs and apply translations to critical admin/auth views in `lang/` and `resources/views/`
- [x] T016 [US1] Expose locale preference controls in `app/Livewire/Admin/` and `resources/views/livewire/admin/`

**Checkpoint**: User Story 1 should provide platform locale switching independently

---

## Phase 4: User Story 2 - Publicar idiomas suportados com cobertura mínima e fallback (Priority: P2)

**Goal**: Publish governed locale bundles with active fallback and measurable coverage for critical strings

**Independent Test**: A support operator can publish a locale bundle, inspect coverage per locale and record missing keys without breaking the current healthy publication

### Tests for User Story 2 ⚠️

- [x] T017 [P] [US2] Create feature test for locale publication workflow in `tests/Feature/PlatformLocalizationPublicationTest.php`
- [x] T018 [P] [US2] Create feature test for coverage snapshot and missing-key report generation in `tests/Feature/PlatformLocalizationCoverageTest.php`
- [x] T019 [P] [US2] Create unit test for publication guardrails and coverage rules in `tests/Unit/PlatformLocalePublicationRulesTest.php`

### Implementation for User Story 2

- [x] T020 [P] [US2] Implement locale coverage and missing-key detection services in `app/Services/Platform/`
- [x] T021 [P] [US2] Implement locale publication/versioning service in `app/Services/Platform/`
- [x] T022 [US2] Implement administrative manager workflow for publication and preference updates in `app/Livewire/Admin/`
- [x] T023 [US2] Implement requests/validation for locale publication and active fallback configuration in `app/Http/Requests/Admin/`
- [x] T024 [US2] Publish platform localization publication events through backbone `010`

**Checkpoint**: User Story 2 should support governed locale rollout independently

---

## Phase 5: User Story 3 - Inspecionar cobertura e reverter publicações degradadas (Priority: P3)

**Goal**: Provide central visibility into locale coverage gaps and allow governed rollback to the last healthy publication

**Independent Test**: A super admin can inspect active locale coverage and execute rollback when a publication is degraded or inconsistent

### Tests for User Story 3 ⚠️

- [x] T025 [P] [US3] Create feature test for localization inspection filters in `tests/Feature/PlatformLocalizationInspectionTest.php`
- [x] T026 [P] [US3] Create feature test for governed locale publication rollback in `tests/Feature/PlatformLocalizationRollbackTest.php`
- [x] T027 [P] [US3] Create unit test for rollback eligibility and missing-key severity rules in `tests/Unit/PlatformLocaleRollbackRulesTest.php`

### Implementation for User Story 3

- [x] T028 [P] [US3] Implement localization inspection summary service in `app/Services/Platform/`
- [x] T029 [P] [US3] Implement locale publication rollback service in `app/Services/Platform/`
- [x] T030 [US3] Implement super admin Livewire dashboard and inspection endpoint for localization governance in `app/Livewire/Admin/` and `app/Http/Controllers/Admin/`
- [x] T031 [US3] Implement rollback controls, missing-key drill-down and summary views in `resources/views/livewire/admin/`
- [x] T032 [US3] Expose central publication history and missing-key evidence for governance support

**Checkpoint**: User Story 3 should make localization governance auditable independently

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T033 [P] Document locale publication, fallback response and rollback procedures in `GO_LIVE_RUNBOOK.md` and related docs
- [x] T034 Update architecture and product documentation for the platform internationalization layer in `ARCHITECTURE.md`, `README.md` and roadmap artifacts when implementation starts
- [x] T035 [P] Add targeted coverage for localization event publication, preference persistence and rollback audit trail in `tests/Feature/` and `tests/Unit/`
- [x] T036 Perform code cleanup and Laravel Pint on changed files
- [x] T037 Run `quickstart.md` validation and record evidence in feature artifacts
