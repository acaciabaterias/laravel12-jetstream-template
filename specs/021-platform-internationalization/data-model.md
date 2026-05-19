# Data Model: Módulo 021 - Platform Internationalization

## Overview

O módulo `021` internacionaliza o plano central usando arquivos `lang/` como fonte de strings e tabelas centrais para preferências, publicações e lacunas detectadas.

## Entities

### UsuarioPlataforma

**Purpose**: operador autenticado do plano central com preferência persistida de idioma.

**Fields added**
- `preferred_locale` (`string`, nullable): locale preferido do operador.

**Validation**
- deve estar dentro da lista suportada em `config/platform_localization.php`
- pode ser `null` para usar a publicação ativa padrão

### PlatformLocalePublicationRecord

**Purpose**: representa uma publicação governada de idiomas suportados com locale padrão, fallback e snapshot de cobertura.

**Fields**
- `id`
- `release_key` (`string`, unique)
- `status` (`draft`, `active`, `superseded`, `rolled_back`)
- `default_locale` (`string`)
- `fallback_locale` (`string`)
- `supported_locales` (`json`)
- `coverage_snapshot` (`json`)
- `published_by` (`foreignId`, nullable, `usuarios_plataforma`)
- `rolled_back_by` (`foreignId`, nullable, `usuarios_plataforma`)
- `published_at` (`timestamp`, nullable)
- `rolled_back_at` (`timestamp`, nullable)
- `superseded_by_publication_id` (`foreignId`, nullable, self reference)
- `metadata` (`json`, nullable)
- timestamps

**Validation**
- `default_locale` e `fallback_locale` devem existir em `supported_locales`
- `supported_locales` deve conter pelo menos um locale
- apenas uma publicação pode estar `active`

### PlatformLocaleMissingKeyReport

**Purpose**: registra lacunas materiais de tradução detectadas por locale e chave obrigatória.

**Fields**
- `id`
- `platform_locale_publication_record_id` (`foreignId`, nullable)
- `locale_code` (`string`)
- `translation_key` (`string`)
- `context_group` (`string`)
- `severity` (`warning`, `critical`)
- `resolution_status` (`open`, `rolled_back`, `accepted`)
- `detected_at` (`timestamp`)
- `resolved_at` (`timestamp`, nullable)
- `resolved_by` (`foreignId`, nullable, `usuarios_plataforma`)
- `metadata` (`json`, nullable)
- timestamps

**Validation**
- `locale_code` deve ser suportado pelo sistema
- `translation_key` deve pertencer à lista governada de chaves obrigatórias

## Relationships

- `UsuarioPlataforma` has many `PlatformLocalePublicationRecord` via `published_by`
- `UsuarioPlataforma` has many `PlatformLocalePublicationRecord` via `rolled_back_by`
- `UsuarioPlataforma` has many `PlatformLocaleMissingKeyReport` via `resolved_by`
- `PlatformLocalePublicationRecord` has many `PlatformLocaleMissingKeyReport`
- `PlatformLocalePublicationRecord` belongs to optional `supersededBy`

## State Transitions

### PlatformLocalePublicationRecord

- `draft` -> `active`
- `active` -> `superseded`
- `active` -> `rolled_back`
- `superseded` -> `active` during governed rollback

### PlatformLocaleMissingKeyReport

- `open` -> `accepted`
- `open` -> `rolled_back`

## Derived Views

### Coverage Snapshot

Array keyed by locale:
- `required_keys`
- `translated_keys`
- `missing_keys`
- `coverage_ratio`

### Active Locale Resolution

Order:
1. `UsuarioPlataforma.preferred_locale` if allowed by active publication
2. `active publication.default_locale`
3. `active publication.fallback_locale`
4. `config('app.fallback_locale')`
