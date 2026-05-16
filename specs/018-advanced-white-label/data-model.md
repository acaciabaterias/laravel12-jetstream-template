# Data Model: Módulo 018 - Advanced White Label Experience

## BrandIdentityProfile

- **Purpose**: representar a identidade visual principal de um tenant.
- **Fields**:
  - `tenant_id`
  - `brand_name`
  - `brand_slug`
  - `primary_logo_asset_id`
  - `secondary_logo_asset_id`
  - `favicon_asset_id`
  - `default_font_family`
  - `status` (`draft`, `active`, `archived`)
  - `notes`
- **Relationships**:
  - hasMany `TenantThemeVersion`
  - hasMany `ThemeAssetRecord`
- **Validation**:
  - `brand_name` obrigatório
  - `brand_slug` único no catálogo central
  - ao menos um ativo principal válido para ativação

## TenantThemeVersion

- **Purpose**: representar uma versão específica de tema aplicável a um tenant.
- **Fields**:
  - `brand_identity_profile_id`
  - `version_label`
  - `theme_tokens` (paleta, tipografia, estados visuais, superfícies)
  - `navigation_preferences`
  - `validation_summary`
  - `status` (`draft`, `published`, `rolled_back`)
  - `published_at`
  - `rolled_back_at`
- **Relationships**:
  - belongsTo `BrandIdentityProfile`
  - hasMany `ThemePublicationRecord`
  - hasMany `ThemeRollbackEvidence`
- **Validation**:
  - `theme_tokens` precisa incluir tokens obrigatórios
  - somente uma versão publicada por identidade

## ThemeAssetRecord

- **Purpose**: representar ativos vinculados à identidade visual.
- **Fields**:
  - `brand_identity_profile_id`
  - `asset_type` (`logo_primary`, `logo_secondary`, `favicon`, `login_background`, `dashboard_mark`)
  - `storage_reference`
  - `mime_type`
  - `checksum`
  - `status`
- **Relationships**:
  - belongsTo `BrandIdentityProfile`
- **Validation**:
  - tipo de ativo obrigatório
  - referência de armazenamento obrigatória

## ThemePublicationRecord

- **Purpose**: representar a publicação auditável de uma versão de tema.
- **Fields**:
  - `tenant_theme_version_id`
  - `environment`
  - `operator_id`
  - `validation_passed`
  - `validation_messages`
  - `published_snapshot`
  - `status` (`pending`, `published`, `rejected`)
  - `published_at`
- **Relationships**:
  - belongsTo `TenantThemeVersion`
- **Validation**:
  - publicação só pode concluir com `validation_passed = true`

## ThemeRollbackEvidence

- **Purpose**: representar a evidência auditável de rollback visual.
- **Fields**:
  - `tenant_theme_version_id`
  - `restored_theme_version_id`
  - `operator_id`
  - `reason`
  - `evidence_payload`
  - `rolled_back_at`
- **Relationships**:
  - belongsTo `TenantThemeVersion`
- **Validation**:
  - motivo obrigatório
  - versão restaurada obrigatória

## State Transitions

- `BrandIdentityProfile.status`: `draft -> active -> archived`
- `TenantThemeVersion.status`: `draft -> published -> rolled_back`
- `ThemePublicationRecord.status`: `pending -> published|rejected`
