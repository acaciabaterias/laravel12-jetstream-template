# Contract: Multi-Currency Inspection

## Endpoint

- `GET /admin/currencies/inspection`

## Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `currency` | string(3) | filtra a moeda suportada ou inconsistente |
| `status` | string | filtra publicação por `active`, `superseded`, `rolled_back` |
| `severity` | string | filtra inconsistências por `warning` ou `critical` |
| `limit` | integer | total máximo de inconsistências retornadas |
| `publication_limit` | integer | total máximo de publicações retornadas |

## Response Shape

```json
{
  "summary": {
    "active_publication_id": 42,
    "base_currency_code": "BRL",
    "default_currency_code": "USD",
    "supported_currencies": ["BRL", "USD", "EUR"],
    "open_issues": 1
  },
  "rates": [
    {
      "currency_code": "USD",
      "rate_against_base": "5.42000000",
      "inverse_rate": "0.18450185",
      "effective_at": "2026-05-18T10:00:00Z"
    }
  ],
  "publications": [
    {
      "id": 42,
      "release_key": "fx-2026-05-18-100000",
      "status": "active",
      "base_currency_code": "BRL",
      "default_currency_code": "USD",
      "supported_currencies": ["BRL", "USD", "EUR"],
      "published_at": "2026-05-18T10:00:00Z"
    }
  ],
  "issues": [
    {
      "currency_code": "EUR",
      "issue_type": "coverage_gap",
      "severity": "warning",
      "resolution_status": "open",
      "detected_at": "2026-05-18T10:00:00Z"
    }
  ]
}
```

## Contract Rules

- `summary.base_currency_code` e `summary.default_currency_code` devem sempre refletir a publicação ativa ou o fallback configurado.
- `rates` deve refletir somente o snapshot ativo quando não houver filtro de publicação histórica.
- `issues` deve permitir leitura operacional sem recalcular a publicação em tempo real.
