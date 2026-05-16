# Data Model: Módulo 014 - Platform Commercial Analytics

## 1. SnapshotAnalyticsComercial

**Purpose**: representar um snapshot central dos indicadores executivos da plataforma em determinada janela.

**Core fields**:
- `id`
- `reference_date`
- `period_start`
- `period_end`
- `mrr_amount`
- `churn_count`
- `churn_rate`
- `delinquent_count`
- `recovered_count`
- `recovered_amount`
- `blocked_count`
- `metadata`
- `created_at`
- `updated_at`

## 2. RecorteCoorteComercial

**Purpose**: representar uma visão segmentada por coorte de entrada, período ou carteira.

**Core fields**:
- `id`
- `snapshot_analytics_comercial_id`
- `cohort_label`
- `cohort_start_date`
- `cohort_end_date`
- `active_subscriptions`
- `cancelled_subscriptions`
- `recovered_subscriptions`
- `delinquent_subscriptions`
- `mrr_amount`
- `metadata`
- `created_at`
- `updated_at`

## 3. MetricChannelPerformance

**Purpose**: representar desempenho comparativo por canal de cobrança ou recuperação.

**Core fields**:
- `id`
- `snapshot_analytics_comercial_id`
- `channel_type` (`billing`, `recovery`)
- `channel_name`
- `total_cases`
- `successful_cases`
- `failed_cases`
- `recovered_amount`
- `conversion_rate`
- `metadata`
- `created_at`
- `updated_at`

## 4. InsightRiscoComercial

**Purpose**: representar agrupamentos centrais de clientes ou assinaturas em risco comercial ou financeiro.

**Core fields**:
- `id`
- `snapshot_analytics_comercial_id`
- `risk_type` (`churn`, `delinquency`, `recovery_stall`, `payment_failure`)
- `severity`
- `total_accounts`
- `total_exposure`
- `description`
- `metadata`
- `created_at`
- `updated_at`

## 5. DrilldownAnalyticsComercial

**Purpose**: representar a ligação entre um indicador agregado e os registros operacionais que o compõem.

**Core fields**:
- `id`
- `snapshot_analytics_comercial_id`
- `source_type`
- `source_id`
- `dimension_type`
- `dimension_value`
- `metric_key`
- `metric_value`
- `metadata`
- `created_at`
- `updated_at`

## Relationships

- `SnapshotAnalyticsComercial` possui muitos `RecorteCoorteComercial`
- `SnapshotAnalyticsComercial` possui muitos `MetricChannelPerformance`
- `SnapshotAnalyticsComercial` possui muitos `InsightRiscoComercial`
- `SnapshotAnalyticsComercial` possui muitos `DrilldownAnalyticsComercial`

## Notes

- Todas as entidades vivem no banco central.
- Snapshots são derivados dos módulos `011`, `012` e `013`.
- Drill-down precisa manter referência auditável ao dado operacional que originou cada recorte.
