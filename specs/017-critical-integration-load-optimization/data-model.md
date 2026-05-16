# Data Model: Módulo 017 - Critical Integration Load Optimization

## 1. LoadScenarioProfile

**Purpose**: representar um cenário de carga reproduzível associado a um fluxo crítico e ambiente.

**Core fields**:
- `id`
- `flow_name`
- `scenario_name`
- `environment`
- `request_budget`
- `duration_seconds`
- `concurrency_level`
- `expected_throughput_per_minute`
- `expected_p95_latency_ms`
- `expected_error_rate`
- `metadata`
- `created_at`
- `updated_at`

## 2. BenchmarkExecutionRecord

**Purpose**: representar uma execução de benchmark com resultado comparável para um cenário registrado.

**Core fields**:
- `id`
- `load_scenario_profile_id`
- `started_at`
- `finished_at`
- `throughput_per_minute`
- `p95_latency_ms`
- `error_rate`
- `status`
- `comparison_status`
- `metadata`
- `created_at`
- `updated_at`

## 3. PerformanceBottleneckRecord

**Purpose**: representar um gargalo identificado durante benchmark, com categoria, impacto e origem.

**Core fields**:
- `id`
- `benchmark_execution_record_id`
- `flow_name`
- `category`
- `component_name`
- `summary`
- `impact_level`
- `evidence_payload`
- `metadata`
- `created_at`
- `updated_at`

## 4. TuningChangeRecord

**Purpose**: representar uma hipótese de tuning, alteração aplicada, ambiente e decisão posterior.

**Core fields**:
- `id`
- `flow_name`
- `environment`
- `change_key`
- `hypothesis_summary`
- `change_type`
- `applied_at`
- `status`
- `baseline_execution_id`
- `validation_execution_id`
- `rollback_recommended`
- `metadata`
- `created_at`
- `updated_at`

## 5. PerformanceRollbackEvidence

**Purpose**: representar a evidência auditável de rollback, revalidação e baseline restaurada após tuning regressivo.

**Core fields**:
- `id`
- `tuning_change_record_id`
- `operator_user_id`
- `recorded_at`
- `result_status`
- `rollback_reason`
- `payload`
- `metadata`
- `created_at`
- `updated_at`

## Relationships

- `LoadScenarioProfile` possui muitas `BenchmarkExecutionRecord`
- `BenchmarkExecutionRecord` possui muitos `PerformanceBottleneckRecord`
- `TuningChangeRecord` referencia benchmark baseline e benchmark de validação
- `PerformanceRollbackEvidence` referencia uma `TuningChangeRecord`

## Notes

- Todas as entidades vivem no banco central.
- O módulo registra benchmark e tuning governado; não substitui profiling externo detalhado.
- A taxonomia de fluxos e severidades deve seguir os módulos `010`, `015` e `016`.
