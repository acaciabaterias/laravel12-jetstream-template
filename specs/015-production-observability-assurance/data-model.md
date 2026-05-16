# Data Model: Módulo 015 - Production Observability Assurance

## 1. OperationalSloDefinition

**Purpose**: representar um objetivo operacional e seus limiares para um fluxo crítico.

**Core fields**:
- `id`
- `flow_name`
- `metric_key`
- `target_value`
- `warning_threshold`
- `critical_threshold`
- `severity_mapping`
- `metadata`
- `created_at`
- `updated_at`

## 2. OperationalAlertSnapshot

**Purpose**: representar um recorte consolidado de saúde operacional em determinada janela.

**Core fields**:
- `id`
- `reference_at`
- `flow_name`
- `status`
- `severity`
- `backlog_count`
- `latency_ms`
- `failure_rate`
- `open_replays`
- `metadata`
- `created_at`
- `updated_at`

## 3. LoadTestBaseline

**Purpose**: representar o baseline aprovado para um cenário crítico de carga.

**Core fields**:
- `id`
- `scenario_name`
- `flow_name`
- `throughput_per_minute`
- `p95_latency_ms`
- `error_rate`
- `environment_notes`
- `accepted_at`
- `metadata`
- `created_at`
- `updated_at`

## 4. OperationalIncidentRecord

**Purpose**: representar um incidente operacional e sua evolução.

**Core fields**:
- `id`
- `incident_key`
- `flow_name`
- `severity`
- `status`
- `opened_at`
- `acknowledged_at`
- `resolved_at`
- `summary`
- `metadata`
- `created_at`
- `updated_at`

## 5. RunbookExecutionEvidence

**Purpose**: representar a trilha de execução de replay, contingência, rollback ou restore validation.

**Core fields**:
- `id`
- `operational_incident_record_id`
- `execution_type`
- `operator_user_id`
- `started_at`
- `finished_at`
- `result_status`
- `evidence_payload`
- `notes`
- `metadata`
- `created_at`
- `updated_at`

## Relationships

- `OperationalIncidentRecord` possui muitas `RunbookExecutionEvidence`
- `OperationalAlertSnapshot` pode originar `OperationalIncidentRecord` por correlação
- `OperationalSloDefinition` governa avaliação de `OperationalAlertSnapshot`
- `LoadTestBaseline` é consultado por fluxo e cenário durante comparações de capacidade

## Notes

- Todas as entidades vivem no banco central.
- Snapshots operacionais são derivados do backbone, métricas centrais e sinais administrativos.
- Evidências precisam manter referência auditável ao incidente e ao operador responsável.
