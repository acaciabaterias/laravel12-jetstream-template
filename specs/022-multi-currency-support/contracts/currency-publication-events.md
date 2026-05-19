# Contract: Multi-Currency Publication Events

## Event 1: `MOEDAS_PLATAFORMA_PUBLICADAS`

```json
{
  "publication_id": 42,
  "release_key": "fx-2026-05-18-100000",
  "base_currency_code": "BRL",
  "default_currency_code": "USD",
  "supported_currencies": ["BRL", "USD", "EUR"],
  "status": "active",
  "occurred_at": "2026-05-18T10:00:00Z",
  "metadata": {
    "published_by": 7,
    "open_issues": 1
  }
}
```

## Event 2: `ROLLBACK_MOEDAS_PLATAFORMA_EXECUTADO`

```json
{
  "publication_id": 42,
  "release_key": "fx-2026-05-18-100000",
  "restored_publication_id": 41,
  "status": "rolled_back",
  "occurred_at": "2026-05-18T10:15:00Z",
  "metadata": {
    "rolled_back_by": 1,
    "reason": "Tabela inconsistente para EUR",
    "restored_default_currency": "BRL"
  }
}
```

## Operational Notes

- Ambos os eventos devem usar `origin_context = platform-currencies`.
- `publication_id` e `restored_publication_id` devem mapear para registros reais do banco central.
- Consumidores do backbone podem reconstruir a moeda ativa sem consultar fontes externas.
