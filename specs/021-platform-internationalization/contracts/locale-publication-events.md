# Contract: Platform Localization Material Events

## Event Types

### `LOCALIZACAO_PLATAFORMA_PUBLICADA`

```json
{
  "publication_id": 12,
  "release_key": "core-i18n-2026-05",
  "default_locale": "pt_BR",
  "fallback_locale": "en",
  "supported_locales": ["pt_BR", "en", "es"],
  "status": "active",
  "occurred_at": "2026-05-18T12:00:00Z",
  "metadata": {
    "published_by": 4,
    "open_missing_keys": 2
  }
}
```

### `ROLLBACK_LOCALIZACAO_PLATAFORMA_EXECUTADO`

```json
{
  "publication_id": 13,
  "release_key": "core-i18n-2026-06",
  "restored_publication_id": 12,
  "status": "rolled_back",
  "occurred_at": "2026-05-18T12:15:00Z",
  "metadata": {
    "rolled_back_by": 1,
    "reason": "fallback inconsistente",
    "restored_default_locale": "pt_BR"
  }
}
```
