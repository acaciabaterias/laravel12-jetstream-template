# Data Model: Módulo 019 - Executive Reporting Hub

## ExecutiveAnalyticsSnapshot

**Purpose**: Representa o recorte consolidado de indicadores executivos calculados para um conjunto específico de filtros.

**Fields**:
- `id`
- `period_start`
- `period_end`
- `plan_filters`
- `channel_filters`
- `portfolio_filters`
- `recovery_status_filters`
- `kpi_payload`
- `drilldown_payload`
- `snapshot_status`
- `generated_at`
- `generated_by`

**Relationships**:
- has many `ExecutiveReportExport`
- can be referenced by many `ExecutiveReportExecutionLog`

**Validation Rules**:
- período obrigatório e coerente
- payload de indicadores não pode estar vazio
- filtros devem ser serializáveis e reexecutáveis

## ExecutiveReportDefinition

**Purpose**: Define um relatório executivo reutilizável, com identidade, recorte esperado e objetivo operacional.

**Fields**:
- `id`
- `slug`
- `name`
- `description`
- `default_filters`
- `visible_sections`
- `supported_formats`
- `status`
- `created_by`

**Relationships**:
- has many `ExecutiveReportExport`

**Validation Rules**:
- `slug` único
- pelo menos um formato suportado
- descrição operacional clara

## ExecutiveReportExport

**Purpose**: Registra uma geração concreta de relatório a partir de um snapshot executivo.

**Fields**:
- `id`
- `executive_analytics_snapshot_id`
- `executive_report_definition_id`
- `format`
- `file_reference`
- `export_status`
- `requested_by`
- `requested_at`
- `completed_at`
- `scope_summary`

**Relationships**:
- belongs to `ExecutiveAnalyticsSnapshot`
- belongs to `ExecutiveReportDefinition`
- has many `ExecutiveReportExecutionLog`

**Validation Rules**:
- formato deve ser `excel` ou `pdf`
- exportação só pode concluir com snapshot consistente
- referência do artefato deve existir apenas quando finalizada

## ExecutiveReportExecutionLog

**Purpose**: Preserva a trilha auditável de geração, falha, reexecução ou reinspeção de um relatório.

**Fields**:
- `id`
- `executive_report_export_id`
- `event_type`
- `operator_name`
- `operator_id`
- `event_payload`
- `logged_at`

**Relationships**:
- belongs to `ExecutiveReportExport`

**Validation Rules**:
- cada log deve indicar tipo de evento
- payload deve conter contexto suficiente para auditoria

## ExecutiveDrilldownView

**Purpose**: Estrutura de detalhamento operacional vinculada a um indicador executivo.

**Fields**:
- `metric_key`
- `label`
- `rows`
- `summary`
- `origin_filters`

**Relationships**:
- embedded within `ExecutiveAnalyticsSnapshot`

**Validation Rules**:
- `metric_key` obrigatório
- origem dos filtros deve corresponder ao snapshot pai
