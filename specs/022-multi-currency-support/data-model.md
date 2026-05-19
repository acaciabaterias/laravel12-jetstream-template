# Data Model: Módulo 022 - Multi-Currency Support

## 1. PlatformCurrencyPreference

**Purpose**: Persistir a moeda de exibição preferida por operador da plataforma.

| Field | Type | Rules |
|-------|------|-------|
| `id` | bigint | PK |
| `usuario_plataforma_id` | bigint | FK obrigatória, única por operador |
| `currency_code` | string(3) | ISO 4217, deve existir na publicação ativa |
| `source` | string | `operator`, `fallback`, `restored` |
| `updated_at` | timestamp | auditoria operacional |
| `created_at` | timestamp | auditoria operacional |

**Relationships**
- pertence a `UsuarioPlataforma`

## 2. PlatformCurrencyCatalogEntry

**Purpose**: Representar uma moeda suportada pela plataforma com seus metadados operacionais.

| Field | Type | Rules |
|-------|------|-------|
| `id` | bigint | PK |
| `currency_code` | string(3) | ISO 4217, único |
| `display_name` | string | obrigatório |
| `symbol` | string | obrigatório |
| `decimal_scale` | unsignedTinyInteger | `0` a `4` |
| `is_base_currency` | boolean | apenas uma base ativa por publicação |
| `metadata` | json | contexto adicional |
| `created_at` | timestamp | auditoria |
| `updated_at` | timestamp | auditoria |

## 3. PlatformExchangeRatePublicationRecord

**Purpose**: Representar a publicação governada do pacote ativo de moedas e taxas.

| Field | Type | Rules |
|-------|------|-------|
| `id` | bigint | PK |
| `release_key` | string | único |
| `status` | string | `draft`, `active`, `superseded`, `rolled_back` |
| `base_currency_code` | string(3) | obrigatório |
| `default_currency_code` | string(3) | obrigatório |
| `supported_currencies` | json | lista não vazia |
| `rate_snapshot` | json | snapshot versionado |
| `coverage_snapshot` | json | cobertura das conversões obrigatórias |
| `published_by` | bigint nullable | FK opcional para `UsuarioPlataforma` |
| `published_at` | timestamp nullable | data da promoção |
| `superseded_by_publication_id` | bigint nullable | FK autorreferente |
| `rolled_back_by` | bigint nullable | FK opcional |
| `rolled_back_at` | timestamp nullable | trilha de rollback |
| `metadata` | json | inconsistências, contagens, motivo |
| `created_at` | timestamp | auditoria |
| `updated_at` | timestamp | auditoria |

**Relationships**
- possui muitas `PlatformExchangeRateEntry`
- possui muitas `PlatformConversionIssueReport`

## 4. PlatformExchangeRateEntry

**Purpose**: Representar a taxa ativa de uma moeda em relação à moeda base da publicação.

| Field | Type | Rules |
|-------|------|-------|
| `id` | bigint | PK |
| `platform_exchange_rate_publication_record_id` | bigint | FK obrigatória |
| `currency_code` | string(3) | obrigatório |
| `rate_against_base` | decimal(18,8) | obrigatório, positivo |
| `inverse_rate` | decimal(18,8) nullable | opcional para inspeção |
| `effective_at` | timestamp | obrigatório |
| `metadata` | json | origem e observações |
| `created_at` | timestamp | auditoria |
| `updated_at` | timestamp | auditoria |

## 5. PlatformConversionIssueReport

**Purpose**: Registrar inconsistências materiais de câmbio ou cobertura da publicação.

| Field | Type | Rules |
|-------|------|-------|
| `id` | bigint | PK |
| `platform_exchange_rate_publication_record_id` | bigint | FK obrigatória |
| `currency_code` | string(3) | obrigatório |
| `issue_type` | string | `missing_rate`, `invalid_rate`, `coverage_gap`, `rollback` |
| `severity` | string | `warning`, `critical` |
| `resolution_status` | string | `open`, `resolved`, `rolled_back` |
| `issue_payload` | json | detalhe material da inconsistência |
| `detected_at` | timestamp | obrigatório |
| `resolved_at` | timestamp nullable | auditoria |
| `resolved_by` | bigint nullable | FK opcional |
| `created_at` | timestamp | auditoria |
| `updated_at` | timestamp | auditoria |

## Derived Rules

- `BRL` começa como moeda base obrigatória do primeiro pacote ativo.
- Toda moeda suportada deve possuir `PlatformExchangeRateEntry` positiva e materializada.
- A moeda padrão deve pertencer à lista `supported_currencies`.
- Rollback marca a publicação candidata como `rolled_back` e reativa a última publicação elegível.
- Preferências monetárias de operador sobrevivem ao rollback, desde que apontem para moeda ainda suportada; caso contrário, caem na moeda padrão ativa.
