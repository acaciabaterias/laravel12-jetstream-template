# Contract: Branding Events

## Eventos materiais esperados

### `TEMA_WHITE_LABEL_PUBLICADO`

- **Origin**: módulo `018`
- **When**: uma versão de tema é validada e publicada para um tenant
- **Payload mínimo**:
  - `tenant_id`
  - `brand_identity_profile_id`
  - `tenant_theme_version_id`
  - `version_label`
  - `environment`
  - `published_at`

### `ROLLBACK_TEMA_WHITE_LABEL_EXECUTADO`

- **Origin**: módulo `018`
- **When**: uma versão publicada é revertida para a última configuração saudável
- **Payload mínimo**:
  - `tenant_id`
  - `tenant_theme_version_id`
  - `restored_theme_version_id`
  - `reason`
  - `rolled_back_at`
