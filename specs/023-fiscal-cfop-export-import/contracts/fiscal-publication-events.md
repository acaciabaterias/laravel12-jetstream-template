# Contract: Fiscal Publication Events

## Event 1: `CATALOGO_FISCAL_PUBLICADO`

```json
{
  "publication_id": 17,
  "release_key": "fiscal-2026-05-18-110000",
  "supported_scenarios": ["direct_export", "resale_import"],
  "status": "active",
  "occurred_at": "2026-05-18T11:00:00Z",
  "metadata": {
    "published_by": 4,
    "open_issues": 1
  }
}
```

## Event 2: `ROLLBACK_CATALOGO_FISCAL_EXECUTADO`

```json
{
  "publication_id": 17,
  "release_key": "fiscal-2026-05-18-110000",
  "restored_publication_id": 16,
  "status": "rolled_back",
  "occurred_at": "2026-05-18T11:20:00Z",
  "metadata": {
    "rolled_back_by": 1,
    "reason": "CFOP incompatível com cenário obrigatório",
    "restored_release_key": "fiscal-2026-05-17-180000"
  }
}
```

## Operational Notes

- Ambos os eventos devem usar `origin_context = platform-fiscal-rules`.
- `publication_id` e `restored_publication_id` devem mapear para registros centrais reais.
- Consumidores do backbone devem conseguir reconstruir a publicação fiscal ativa apenas com o evento e o catálogo central.
