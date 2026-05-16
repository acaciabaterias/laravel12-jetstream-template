# Data Model: Módulo 010 - Backbone de Integração e Observabilidade

## 1. EventoOutbox

**Purpose**: registrar eventos de domínio gerados pelo ERP antes da publicação no broker.

**Core fields**:
- `id`
- `tenant_id_logico` ou referência equivalente de contexto resolvido em runtime
- `event_type`
- `event_version`
- `aggregate_type`
- `aggregate_id`
- `idempotency_key`
- `correlation_id`
- `causation_id`
- `payload`
- `headers`
- `status` (`pending`, `publishing`, `published`, `failed`, `dead_letter`)
- `published_at`
- `last_error`
- `attempts`
- `next_attempt_at`
- `created_at`
- `updated_at`

**Validation rules**:
- `event_type`, `event_version`, `idempotency_key` e `correlation_id` obrigatórios
- `idempotency_key` única por tenant + tipo + versão
- `payload` obrigatório e íntegro segundo contrato registrado

## 2. EventoInbox

**Purpose**: controlar consumo idempotente de eventos recebidos externamente.

**Core fields**:
- `id`
- `tenant_id_logico`
- `source_service`
- `event_type`
- `event_version`
- `external_message_id`
- `idempotency_key`
- `correlation_id`
- `payload`
- `status` (`received`, `processing`, `processed`, `ignored_duplicate`, `failed`, `dead_letter`)
- `processed_at`
- `last_error`
- `attempts`
- `created_at`
- `updated_at`

**Validation rules**:
- `external_message_id` ou `idempotency_key` obrigatório
- unicidade por tenant + origem + identificador externo

## 3. EntregaIntegracao

**Purpose**: registrar cada tentativa de transporte, consumo, retry, replay ou dead-letter.

**Core fields**:
- `id`
- `direction` (`outbound`, `inbound`)
- `source_type` (`outbox`, `inbox`, `gateway`)
- `source_id`
- `target_service`
- `transport_kind` (`broker`, `http`, `webhook`)
- `status` (`queued`, `sent`, `acknowledged`, `retried`, `failed`, `dead_letter`, `replayed`)
- `attempt_number`
- `started_at`
- `finished_at`
- `latency_ms`
- `error_code`
- `error_message`
- `operator_user_id`
- `created_at`

**Relationships**:
- pertence a um `EventoOutbox` ou `EventoInbox`
- pode apontar para um operador em replay manual

## 4. ContratoEvento

**Purpose**: catálogo central de contratos de eventos aceitos/publicados pelo ERP.

**Core fields**:
- `id`
- `event_type`
- `event_version`
- `direction` (`publish`, `consume`, `bidirectional`)
- `producer_module`
- `consumer_services`
- `schema_reference`
- `sample_payload`
- `status` (`draft`, `active`, `deprecated`)
- `compatibility_notes`
- `created_at`
- `updated_at`

**Validation rules**:
- único por `event_type + event_version + direction`
- não permitir ativação sem payload de referência

## 5. EndpointIntegracao

**Purpose**: representar destinos síncronos governados pelo gateway.

**Core fields**:
- `id`
- `service_name`
- `route_name`
- `method`
- `target_url`
- `auth_mode`
- `timeout_ms`
- `rate_limit_profile`
- `circuit_breaker_profile`
- `status` (`active`, `disabled`, `degraded`)
- `created_at`
- `updated_at`

## State Transitions

### EventoOutbox
- `pending` → `publishing`
- `publishing` → `published`
- `publishing` → `failed`
- `failed` → `publishing`
- `failed` → `dead_letter`
- `dead_letter` → `publishing` via replay

### EventoInbox
- `received` → `processing`
- `processing` → `processed`
- `processing` → `ignored_duplicate`
- `processing` → `failed`
- `failed` → `processing`
- `failed` → `dead_letter`

## Notes

- O contexto tenant-aware continua obrigatório; nenhuma entidade do backbone deve reintroduzir `filial_id` como mecanismo de isolamento principal.
- O catálogo de contratos deve refletir os eventos já previstos entre ERP e microserviços, começando pelos fluxos dos módulos `005-009`.
