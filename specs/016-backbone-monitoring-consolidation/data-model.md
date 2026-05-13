# Data Model: Módulo 016 - Backbone Monitoring Consolidation

## 1. MonitoringTargetCatalog

**Purpose**: representar um target ou exporter monitorado, associado a um fluxo crítico e ambiente.

**Core fields**:
- `id`
- `flow_name`
- `target_name`
- `environment`
- `endpoint`
- `collector_type`
- `status`
- `metadata`
- `created_at`
- `updated_at`

## 2. MonitoringProbeSnapshot

**Purpose**: representar o último estado conhecido de coleta de um target.

**Core fields**:
- `id`
- `monitoring_target_catalog_id`
- `reference_at`
- `scrape_status`
- `latency_ms`
- `sample_count`
- `failure_reason`
- `metadata`
- `created_at`
- `updated_at`

## 3. AlertRuleDefinition

**Purpose**: representar uma regra de alerta versionada ligada a um fluxo e a um pacote de monitoramento.

**Core fields**:
- `id`
- `flow_name`
- `rule_name`
- `severity`
- `version`
- `condition_summary`
- `status`
- `metadata`
- `created_at`
- `updated_at`

## 4. DashboardProvisioningRecord

**Purpose**: representar um pacote de dashboards ou alertas provisionado em determinado ambiente.

**Core fields**:
- `id`
- `package_name`
- `version`
- `environment`
- `applied_at`
- `validated_at`
- `rollback_version`
- `status`
- `metadata`
- `created_at`
- `updated_at`

## 5. MonitoringReadinessEvidence

**Purpose**: representar a evidência auditável de provisão, rollback, validação e readiness da malha de monitoramento.

**Core fields**:
- `id`
- `environment`
- `evidence_type`
- `operator_user_id`
- `recorded_at`
- `result_status`
- `payload`
- `notes`
- `metadata`
- `created_at`
- `updated_at`

## Relationships

- `MonitoringTargetCatalog` possui muitos `MonitoringProbeSnapshot`
- `AlertRuleDefinition` referencia um `flow_name` já usado pelo backbone e pela observabilidade operacional
- `DashboardProvisioningRecord` pode originar múltiplas `MonitoringReadinessEvidence`
- `MonitoringReadinessEvidence` pode referenciar rollback, validação ou provisão do mesmo pacote de dashboards

## Notes

- Todas as entidades vivem no banco central.
- O modelo registra readiness e governança; não substitui a retenção histórica do Prometheus.
- A taxonomia de fluxos deve seguir o backbone `010` e o módulo `015`.
