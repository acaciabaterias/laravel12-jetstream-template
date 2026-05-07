# Tasks: Módulo 010 - Backbone de Integração e Observabilidade

**Input**: Design documents from `/specs/010-integration-backbone/`
**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/`

**Tests**: Every feature MUST include explicit test tasks. Tests are mandatory.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (`US1`, `US2`, `US3`)
- Include exact file paths in descriptions

## Path Conventions

- Laravel application code in `app/`
- Tenant migrations in `database/migrations/tenant/`
- HTTP routes in `routes/`
- Feature tests in `tests/Feature/`
- Unit tests in `tests/Unit/`

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare feature scaffold and shared references

- [x] T001 Create integration service namespace scaffolding in `app/Services/Integration/` and contract namespace scaffolding in `app/Services/Contracts/`
- [x] T002 Create feature test namespace baseline for integration backbone in `tests/Feature/`
- [x] T003 [P] Create unit test namespace baseline for integration contracts in `tests/Unit/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure required before any user story implementation

**⚠️ CRITICAL**: No user story work should begin until this phase is complete

- [x] T004 Create tenant migrations for `evento_outboxes`, `evento_inboxes`, `entregas_integracao`, `contratos_evento` and `endpoints_integracao` in `database/migrations/tenant/`
- [x] T005 [P] Create Eloquent models `EventoOutbox`, `EventoInbox`, `EntregaIntegracao`, `ContratoEvento` and `EndpointIntegracao` in `app/Models/`
- [x] T006 [P] Create shared enums/value objects for status, direction and transport kind in `app/Support/Integration/`
- [x] T007 Create base service contracts for publisher, consumer, replay and contract registry in `app/Services/Contracts/`
- [ ] T008 Create central integration configuration entries in `config/services.php` and `config/horizon.php` for broker/gateway/backbone queues
- [x] T009 Create foundational observability hooks and reusable metrics recorder in `app/Services/Integration/IntegrationMetrics.php`
- [x] T010 Create authorization policy/gate baseline for operational replay and inspection in `app/Policies/` and `app/Providers/AuthServiceProvider.php`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Publicação confiável de eventos (Priority: P1) 🎯 MVP

**Goal**: Publish ERP domain events through a reliable tenant-aware outbox pipeline

**Independent Test**: A completed ERP action can persist an event to outbox, dispatch it asynchronously, retry on transient failure and mark delivery state without losing tenant context

### Tests for User Story 1 ⚠️

- [x] T011 [P] [US1] Create feature test for outbox persistence and tenant isolation in `tests/Feature/IntegrationBackbonePublicationTest.php`
- [x] T012 [P] [US1] Create feature test for retry and dead-letter transition in `tests/Feature/IntegrationBackboneRetryTest.php`
- [x] T013 [P] [US1] Create unit test for idempotency key generation and event envelope normalization in `tests/Unit/IntegrationEventEnvelopeTest.php`

### Implementation for User Story 1

- [x] T014 [P] [US1] Implement `EventContractRegistry` in `app/Services/Integration/EventContractRegistry.php`
- [x] T015 [P] [US1] Implement `OutboxEventFactory` in `app/Services/Integration/OutboxEventFactory.php`
- [x] T016 [US1] Implement transactional publisher `EventPublisher` in `app/Services/Integration/EventPublisher.php` (depends on T014, T015)
- [x] T017 [US1] Implement broker dispatcher job `DispatchOutboxEventJob` in `app/Jobs/DispatchOutboxEventJob.php`
- [x] T018 [US1] Implement delivery tracking service `OutboundDeliveryTracker` in `app/Services/Integration/OutboundDeliveryTracker.php`
- [x] T019 [US1] Wire publication flow into at least one existing producer path from modules `005-009` in `app/Jobs/ConvertValeToPedidoJob.php`, `app/Jobs/RetryOrchestratorJob.php` or equivalent producer entrypoint
- [x] T020 [US1] Register queue bindings and scheduling for outbox dispatch in `routes/console.php` or `app/Console/Commands/`

**Checkpoint**: User Story 1 should publish reliable events independently

---

## Phase 4: User Story 2 - Consumo idempotente e replay operacional (Priority: P2)

**Goal**: Consume inbound events safely and allow controlled replay of failures

**Independent Test**: The ERP can receive the same event twice without duplicated business effects, move unrecoverable events to dead-letter and replay them with an operator audit trail

### Tests for User Story 2 ⚠️

- [x] T021 [P] [US2] Create feature test for inbound duplicate suppression in `tests/Feature/IntegrationBackboneInboxTest.php`
- [x] T022 [P] [US2] Create feature test for dead-letter replay flow in `tests/Feature/IntegrationBackboneReplayTest.php`
- [x] T023 [P] [US2] Create unit test for replay authorization and state transition rules in `tests/Unit/IntegrationReplayPolicyTest.php`

### Implementation for User Story 2

- [x] T024 [P] [US2] Implement inbound consumer service `InboundEventConsumer` in `app/Services/Integration/InboundEventConsumer.php`
- [x] T025 [P] [US2] Implement inbox deduplication service `InboxDeduplicator` in `app/Services/Integration/InboxDeduplicator.php`
- [x] T026 [US2] Implement replay service `IntegrationReplayService` in `app/Services/Integration/IntegrationReplayService.php`
- [x] T027 [US2] Implement operator command for replaying dead-letter events in `app/Console/Commands/ReplayIntegrationEventCommand.php`
- [x] T028 [US2] Implement manual replay UI action and operational listing in `app/Livewire/IntegrationBackboneDashboard.php`
- [x] T029 [US2] Persist operator audit trail for replay and failure handling using `EntregaIntegracao` and existing audit infrastructure

**Checkpoint**: User Story 2 should process inbound events and replay failures independently

---

## Phase 5: User Story 3 - Visibilidade e contratos compartilhados (Priority: P3)

**Goal**: Expose event contracts and operational observability for ERP-to-microservice integrations

**Independent Test**: Operators can inspect contracts, status, latency, retries and dead-letter items per event and tenant without relying on raw logs

### Tests for User Story 3 ⚠️

- [x] T030 [P] [US3] Create feature test for operational dashboard visibility by role in `tests/Feature/IntegrationBackboneDashboardTest.php`
- [x] T031 [P] [US3] Create feature test for gateway inspection endpoint and filters in `tests/Feature/IntegrationGatewayInspectionTest.php`
- [x] T032 [P] [US3] Create unit test for metrics aggregation and contract catalog consistency in `tests/Unit/IntegrationContractCatalogTest.php`

### Implementation for User Story 3

- [x] T033 [P] [US3] Implement contract catalog CRUD/read service in `app/Services/Integration/EventContractCatalogService.php`
- [x] T034 [P] [US3] Implement gateway registry service in `app/Services/Integration/IntegrationGatewayRegistry.php`
- [x] T035 [US3] Implement integration inspection controller in `app/Http/Controllers/Api/IntegrationInspectionController.php`
- [x] T036 [US3] Implement request validation for inspection filters and replay actions in `app/Http/Requests/IntegrationInspectionRequest.php` and `app/Http/Requests/ReplayIntegrationEventRequest.php`
- [x] T037 [US3] Register operational routes in `routes/api.php` and `routes/web.php`
- [x] T038 [US3] Implement Livewire operational dashboard in `app/Livewire/IntegrationBackboneDashboard.php`
- [x] T039 [US3] Expose Prometheus-compatible backbone metrics in `app/Services/Integration/IntegrationMetrics.php` and related middleware/hooks

**Checkpoint**: User Story 3 should provide contract visibility and operational observability independently

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T040 [P] Document backbone operational flows and replay process updates in `GO_LIVE_RUNBOOK.md` and `MONITORING_SETUP.md`
- [x] T041 Update integration architecture overview in `ARCHITECTURE.md` and `README.md`
- [x] T042 [P] Add additional unit coverage for envelope serialization, contract compatibility and metrics formatting in `tests/Unit/`
- [x] T043 Perform code cleanup and Laravel Pint on changed files
- [x] T044 Run `quickstart.md` validation and record evidence in the feature artifacts

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies
- **Foundational (Phase 2)**: Depends on Setup completion and blocks all user stories
- **User Stories (Phase 3-5)**: Depend on Foundational completion
- **Polish (Phase 6)**: Depends on desired user stories being complete

### User Story Dependencies

- **US1 (P1)**: Starts after Foundational and establishes MVP
- **US2 (P2)**: Starts after Foundational, builds on outbox/inbox models from US1 but remains independently testable
- **US3 (P3)**: Starts after Foundational and can overlap late US2 work once metrics/contracts are available

### Parallel Opportunities

- T005, T006 and T007 can run in parallel after migrations are planned
- All tests inside each user story marked `[P]` can run in parallel
- T014 and T015 can run in parallel before T016
- T024 and T025 can run in parallel before T026
- T033 and T034 can run in parallel before T035 and T038

## Implementation Strategy

### MVP First (US1 only)

1. Complete Setup
2. Complete Foundational phase
3. Deliver US1 with reliable outbox publication
4. Validate publication, retry and tenant isolation before expanding

### Incremental Delivery

1. Add US1 for reliable publication
2. Add US2 for inbound idempotency and replay
3. Add US3 for contract governance and observability
4. Finish with documentation, metrics validation and cleanup

## Notes

- Tests must fail before implementation begins
- No task should reintroduce `filial_id` as the isolation mechanism
- Replay and dead-letter flows require explicit authorization and auditability
- Backup/restore and rollback evidence must be updated when persistent integration state is introduced
