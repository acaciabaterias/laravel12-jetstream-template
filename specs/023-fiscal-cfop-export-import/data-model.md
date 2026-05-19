# Data Model: Módulo 023 - Fiscal CFOP Export/Import

## 1. FiscalCfopCatalogEntry

**Purpose**: Representar um CFOP publicado com direção fiscal e metadados operacionais.

| Field | Type | Rules |
|-------|------|-------|
| `id` | bigint | PK |
| `cfop_code` | string(4) | obrigatório, único por publicação |
| `description` | string | obrigatório |
| `operation_direction` | string | `export`, `import`, `domestic_out`, `domestic_in` |
| `is_enabled` | boolean | default true |
| `metadata` | json | flags complementares |
| `created_at` | timestamp | auditoria |
| `updated_at` | timestamp | auditoria |

## 2. FiscalOperationScenario

**Purpose**: Representar um cenário fiscal obrigatório ou opcional de exportação/importação.

| Field | Type | Rules |
|-------|------|-------|
| `id` | bigint | PK |
| `scenario_key` | string | único |
| `display_name` | string | obrigatório |
| `operation_direction` | string | obrigatório |
| `is_required` | boolean | default false |
| `metadata` | json | critérios do cenário |
| `created_at` | timestamp | auditoria |
| `updated_at` | timestamp | auditoria |

## 3. FiscalRulePublicationRecord

**Purpose**: Representar a publicação governada do catálogo fiscal e dos cenários ativos.

| Field | Type | Rules |
|-------|------|-------|
| `id` | bigint | PK |
| `release_key` | string | único |
| `status` | string | `draft`, `active`, `superseded`, `rolled_back` |
| `supported_scenarios` | json | lista não vazia |
| `catalog_snapshot` | json | snapshot versionado |
| `coverage_snapshot` | json | cobertura dos cenários obrigatórios |
| `published_by` | bigint nullable | FK opcional para `UsuarioPlataforma` |
| `published_at` | timestamp nullable | data da promoção |
| `rolled_back_by` | bigint nullable | FK opcional |
| `rolled_back_at` | timestamp nullable | trilha de rollback |
| `superseded_by_publication_id` | bigint nullable | FK autorreferente |
| `metadata` | json | contagens e observações |
| `created_at` | timestamp | auditoria |
| `updated_at` | timestamp | auditoria |

## 4. FiscalRuleMapping

**Purpose**: Vincular cenário fiscal a CFOP, classificação e flags materiais da publicação.

| Field | Type | Rules |
|-------|------|-------|
| `id` | bigint | PK |
| `fiscal_rule_publication_record_id` | bigint | FK obrigatória |
| `scenario_key` | string | obrigatório |
| `cfop_code` | string(4) | obrigatório |
| `classification_code` | string nullable | NCM/flag complementar quando aplicável |
| `operation_direction` | string | obrigatório |
| `validation_flags` | json | requisitos de conformidade |
| `metadata` | json | observações |
| `created_at` | timestamp | auditoria |
| `updated_at` | timestamp | auditoria |

## 5. FiscalRuleIssueReport

**Purpose**: Registrar inconsistência material ou lacuna fiscal detectada na publicação.

| Field | Type | Rules |
|-------|------|-------|
| `id` | bigint | PK |
| `fiscal_rule_publication_record_id` | bigint nullable | FK opcional |
| `scenario_key` | string | obrigatório |
| `issue_type` | string | `missing_scenario`, `invalid_cfop`, `direction_mismatch`, `rollback` |
| `severity` | string | `warning`, `critical` |
| `resolution_status` | string | `open`, `resolved`, `rolled_back` |
| `issue_payload` | json | detalhe material |
| `detected_at` | timestamp | obrigatório |
| `resolved_at` | timestamp nullable | auditoria |
| `resolved_by` | bigint nullable | FK opcional |
| `created_at` | timestamp | auditoria |
| `updated_at` | timestamp | auditoria |

## Derived Rules

- Toda publicação ativa deve cobrir os cenários obrigatórios definidos em configuração.
- Um `scenario_key` obrigatório não pode permanecer sem mapping ou sem CFOP válido.
- Rollback marca a publicação candidata como `rolled_back` e reativa a última publicação elegível.
- O módulo opera como catálogo governado; o uso transacional definitivo continua no fluxo fiscal do `009`.
