# Tasks: Módulo 006 - Logística e App do Entregador

**Feature Branch**: `006-logistics-delivery-app`
**Spec File**: [spec.md](spec.md)

## Constitution Traceability

- **Multi-Tenancy Isolado (v2.0.0)**: T001-T006, T010-T027
- **Mobile-First Field Operations**: T007-T020, T028-T035
- **Comprehensive Inventory & Reverse Logistics**: T013-T020, T029-T034
- **Proactive Quality & Customer Service**: T012-T027, T030-T035

## Phase 1: Database Migrations (Tenant)

- [x] T001: Criar migration `create_rotas_entrega_table`
- [x] T002: Criar migration `create_pontos_entrega_table`
- [x] T003: Criar migration `create_recebimentos_moveis_table`
- [x] T004: Criar migration `create_geolocalizacao_eventos_table`
- [x] T005: Criar migration `create_sync_eventos_table`
- [x] T006: Criar migration `create_audit_logs_table`

## Phase 2: Models, Services e Jobs

- [x] T007: Criar Model `RotaEntrega`
- [x] T008: Criar Model `PontoEntrega`
- [x] T009: Criar Model `RecebimentoMovel`
- [x] T010: Criar Model `GeolocalizacaoEvento`
- [x] T011: Criar Model `SyncEvento`
- [x] T012: Criar service `RouteCloseValidator`
- [x] T013: Criar service `DeliverySyncService`
- [x] T014: Criar Trait `Auditable`
- [x] T015: Criar job `SyncDeliveryEventsJob`

## Phase 3: Painel Web e Rotas

- [x] T016: Criar Livewire component `RoutePlanner`
- [x] T017: Criar Livewire component `LogisticsDashboard`
- [x] T018: Implementar montagem de rotas com múltiplos pontos
- [x] T019: Implementar visualização tática de status e eventos de rota

## Phase 4: App do Entregador e Offline

- [x] T020: Criar Livewire/PWA screen `DeliveryRouteScreen`
- [x] T021: Configurar PWA com Service Worker e IndexedDB
- [x] T022: Implementar persistência offline de alterações da rota
- [x] T023: Implementar sincronização ordenada ao reconectar
- [x] T024: Implementar recebimentos múltiplos no app móvel
- [x] T025: Implementar ajuste de sucata em campo com recálculo

## Phase 5: Fechamento, Auditoria e Integrações

- [x] T026: Implementar bloqueio de encerramento com divergência operacional
- [x] T027: Integrar ajustes de sucata e recebimentos com módulos 004 e 005
- [x] T028: Registrar auditoria de rota, ponto, recebimento e sincronização crítica

## Phase 6: Tests

- [x] T029: Testar operação offline por turno completo com sincronização posterior
- [x] T030: Testar idempotência da sincronização para evitar duplicidade
- [x] T031: Testar ajuste de sucata com recálculo financeiro
- [x] T032: Testar recebimento móvel com pagamento particionado
- [x] T033: Testar bloqueio de encerramento com divergência
- [x] T034: Testar rastreamento operacional com persistência apenas de eventos relevantes
- [x] T035: Testar isolamento entre tenants sem cross-access
