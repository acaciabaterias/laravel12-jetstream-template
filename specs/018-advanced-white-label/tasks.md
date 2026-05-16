# Tasks: Módulo 018 - Advanced White Label Experience

**Input**: Design documents from `/specs/018-advanced-white-label/`  
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

**Purpose**: Prepare advanced white label namespace and planning references

- [x] T001 Create white label service namespace scaffolding in `app/Services/Operations/` and related central test baselines in `tests/`
- [x] T002 [P] Register feature documentation references and plan pointer consistency in `AGENTS.md`, `ROADMAP.md` and `specs/018-advanced-white-label/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core central infrastructure required before user story work

**⚠️ CRITICAL**: No user story work should begin until this phase is complete

- [x] T003 Create central migrations for `brand_identity_profiles`, `tenant_theme_versions`, `theme_asset_records`, `theme_publication_records` and `theme_rollback_evidences` in `database/migrations/central/`
- [x] T004 [P] Create Eloquent models for brand identities, theme versions, assets, publications and rollback evidences in `app/Models/`
- [x] T005 [P] Create shared enums/value objects for branding state, theme publication status and rollback lifecycle in `app/Support/`
- [x] T006 Create authorization policy/gate baseline for advanced white label operations in `app/Policies/` and `app/Providers/AppServiceProvider.php`
- [x] T007 Create configuration entries for required theme tokens, contrast guardrails and fallback branding guidance in `config/`
- [x] T008 Create foundational branding event publication hooks in `app/Services/Operations/`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Configurar identidade visual reutilizável por tenant (Priority: P1) 🎯 MVP

**Goal**: Register reusable brand identity, assets and draft themes per tenant

**Independent Test**: An operator can persist one tenant brand identity with tokens and assets without affecting any other tenant

### Tests for User Story 1 ⚠️

- [x] T009 [P] [US1] Create feature test for brand identity persistence and tenant isolation in `tests/Feature/AdvancedWhiteLabelBrandIdentityTest.php`
- [x] T010 [P] [US1] Create feature test for white label dashboard visibility and access control in `tests/Feature/AdvancedWhiteLabelDashboardTest.php`
- [x] T011 [P] [US1] Create unit test for required theme token normalization rules in `tests/Unit/AdvancedWhiteLabelThemeTokenRulesTest.php`

### Implementation for User Story 1

- [x] T012 [P] [US1] Implement brand identity composition services in `app/Services/Operations/`
- [x] T013 [P] [US1] Implement draft theme registration workflow in `app/Services/Operations/`
- [x] T014 [US1] Implement white label dashboard in `app/Livewire/Admin/`
- [x] T015 [US1] Persist branding assets and draft theme state for tenant-specific operation
- [x] T016 [US1] Expose reusable brand identity queries for dashboard and inspection layers

**Checkpoint**: User Story 1 should provide reusable tenant branding governance independently

---

## Phase 4: User Story 2 - Publicar tema com governança e validação operacional (Priority: P2)

**Goal**: Validate and publish tenant theme versions with explicit operational checks

**Independent Test**: A technical operator can validate a draft theme, publish a healthy version and inspect the publication result without manual theme editing

### Tests for User Story 2 ⚠️

- [x] T017 [P] [US2] Create feature test for theme publication validation and persistence in `tests/Feature/AdvancedWhiteLabelPublicationTest.php`
- [x] T018 [P] [US2] Create feature test for branding inspection filters by tenant and publication status in `tests/Feature/AdvancedWhiteLabelInspectionFilterTest.php`
- [x] T019 [P] [US2] Create unit test for contrast and required token validation rules in `tests/Unit/AdvancedWhiteLabelPublicationRulesTest.php`

### Implementation for User Story 2

- [x] T020 [P] [US2] Implement branding validation and publication service in `app/Services/Operations/`
- [x] T021 [P] [US2] Implement advanced white label inspection service in `app/Services/Operations/`
- [x] T022 [US2] Implement dashboard filters and publication controls in `app/Livewire/Admin/` and `resources/views/livewire/admin/`
- [x] T023 [US2] Implement requests/validation for white label inspection queries in `app/Http/Requests/`
- [x] T024 [US2] Publish branding publication events through backbone `010`

**Checkpoint**: User Story 2 should support governed theme publication independently

---

## Phase 5: User Story 3 - Reverter branding com evidência auditável (Priority: P3)

**Goal**: Track rollback of white label themes and preserve healthy visual state with auditability

**Independent Test**: An operator can rollback a problematic theme version and confirm the restored version and evidence through central inspection

### Tests for User Story 3 ⚠️

- [x] T025 [P] [US3] Create feature test for white label rollback inspection in `tests/Feature/AdvancedWhiteLabelRollbackInspectionTest.php`
- [x] T026 [P] [US3] Create feature test for fallback branding restoration evidence in `tests/Feature/AdvancedWhiteLabelFallbackRestorationTest.php`
- [x] T027 [P] [US3] Create unit test for rollback lifecycle and active version restoration rules in `tests/Unit/AdvancedWhiteLabelRollbackRulesTest.php`

### Implementation for User Story 3

- [x] T028 [P] [US3] Implement theme rollback lifecycle service in `app/Services/Operations/`
- [x] T029 [P] [US3] Implement rollback evidence recording service in `app/Services/Operations/`
- [x] T030 [US3] Implement inspection endpoint/controller or reusable query service in `app/Http/Controllers/` or `app/Services/`
- [x] T031 [US3] Implement rollback exposure in dashboard/admin operations in `app/Livewire/Admin/`
- [x] T032 [US3] Expose central rollback and restored-theme inspection for operational support

**Checkpoint**: User Story 3 should make white label rollback governance auditable independently

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T033 [P] Document white label publication, validation and rollback procedures in `GO_LIVE_RUNBOOK.md` and related docs
- [x] T034 Update architecture and product documentation for the advanced white label layer in `ARCHITECTURE.md`, `README.md` and roadmap artifacts when implementation starts
- [x] T035 [P] Add targeted coverage for branding event publication, theme serialization and rollback audit trail in `tests/Feature/` and `tests/Unit/`
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

- **US1 (P1)**: Starts after Foundational and establishes reusable tenant branding governance
- **US2 (P2)**: Starts after Foundational and depends logically on brand identity visibility from US1
- **US3 (P3)**: Starts after Foundational and can overlap late US2 work once publication history exists

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
3. Deliver US1 with reusable tenant branding governance
4. Validate consistency against multi-tenant and administrative shell constraints before expanding publication lifecycle

### Incremental Delivery

1. Add US1 for brand identity and draft theme registration
2. Add US2 for validation, publication and inspection
3. Add US3 for rollback governance and restored-theme evidence
4. Finish with documentation, audit refinement and quickstart validation

## Notes

- Tests must fail before implementation begins
- No task should move branding source of truth fully outside the ERP
- Tokens, validation and fallback rules must remain explicit and reusable
- Backup/restore and rollback evidence must be updated when white label publication is introduced
