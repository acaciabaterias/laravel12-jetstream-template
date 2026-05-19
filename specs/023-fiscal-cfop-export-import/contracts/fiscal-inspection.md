# Contract: Fiscal Inspection

## Endpoint

- `GET /admin/fiscal-rules/inspection`

## Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `scenario` | string | filtra cenário fiscal |
| `status` | string | filtra publicação por `active`, `superseded`, `rolled_back` |
| `severity` | string | filtra inconsistências por `warning` ou `critical` |
| `limit` | integer | total máximo de inconsistências retornadas |
| `publication_limit` | integer | total máximo de publicações retornadas |

## Response Shape

```json
{
  "summary": {
    "active_publication_id": 17,
    "required_scenarios": ["direct_export", "resale_import"],
    "covered_scenarios": 4,
    "open_issues": 1
  },
  "mappings": [
    {
      "scenario_key": "direct_export",
      "cfop_code": "7101",
      "operation_direction": "export",
      "validation_flags": {
        "requires_ncm": true,
        "requires_foreign_partner": true
      }
    }
  ],
  "publications": [
    {
      "id": 17,
      "release_key": "fiscal-2026-05-18-110000",
      "status": "active",
      "published_at": "2026-05-18T11:00:00Z"
    }
  ],
  "issues": [
    {
      "scenario_key": "resale_import",
      "issue_type": "missing_scenario",
      "severity": "warning",
      "resolution_status": "open"
    }
  ]
}
```

## Contract Rules

- `summary.required_scenarios` deve refletir a configuração ativa do recorte obrigatório.
- `mappings` deve refletir apenas a publicação ativa quando não houver consulta histórica específica.
- `issues` deve ser materializado a partir da publicação, não recalculado integralmente por request.
