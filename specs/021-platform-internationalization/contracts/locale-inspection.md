# Contract: Platform Localization Inspection

## Endpoint

- `GET /admin/localization/inspection`

## Purpose

Expose reusable central inspection for active locale publication, coverage and missing key reports.

## Query Parameters

- `locale` (`string`, optional)
- `status` (`string`, optional)
- `severity` (`string`, optional)

## Response Shape

```json
{
  "summary": {
    "active_publication_id": 12,
    "default_locale": "pt_BR",
    "fallback_locale": "en",
    "supported_locales": ["pt_BR", "en", "es"],
    "open_missing_keys": 4
  },
  "coverage": [
    {
      "locale": "pt_BR",
      "required_keys": 24,
      "translated_keys": 24,
      "missing_keys": 0,
      "coverage_ratio": 1
    }
  ],
  "publications": [
    {
      "id": 12,
      "release_key": "core-i18n-2026-05",
      "status": "active",
      "default_locale": "pt_BR",
      "fallback_locale": "en"
    }
  ],
  "missing_key_reports": [
    {
      "locale_code": "es",
      "translation_key": "Dashboard da Plataforma",
      "severity": "warning",
      "resolution_status": "open"
    }
  ]
}
```
