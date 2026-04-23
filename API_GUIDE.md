# API Guide

## Visao Geral

Este guia resume os principais endpoints do ERP Core e dos microservicos com exemplos de requisicao e resposta.

Ha dois modos de exposicao:

- direto no servico: `http://localhost:800X/api/v1/...`
- via gateway, conforme `openapi.yaml`: `http://localhost:800X/ms-00X/v1/...`

Os exemplos abaixo usam o acesso direto.

## Convencoes

- payloads estao em JSON
- exemplos de resposta sao ilustrativos, mas baseados nas rotas, testes e schemas atuais do repositorio
- endpoints do ERP Core protegidos usam Sanctum Bearer Token

## ERP Core

### `GET /api/user`

Retorna o usuario autenticado do ERP.

```bash
curl -s \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Accept: application/json" \
  http://localhost:8000/api/user
```

Resposta exemplo:

```json
{
  "id": 1,
  "name": "Admin ERP",
  "email": "admin@example.com"
}
```

### `GET /api/sync/mobile`

Sincroniza dados base para uso offline no app.

```bash
curl -s \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Accept: application/json" \
  http://localhost:8000/api/sync/mobile
```

Resposta exemplo:

```json
{
  "fabricantes": [
    {
      "id": 1,
      "nome": "Moura"
    }
  ],
  "veiculos": [
    {
      "id": 1,
      "fabricante_id": 1,
      "modelo": "Onix",
      "motorizacao": "1.0",
      "ano_inicio": 2020,
      "ano_fim": 2024,
      "baterias": [
        {
          "id": 10,
          "sku": "BAT-001",
          "marca": "Moura",
          "tecnologia": "AGM",
          "polo": "D"
        }
      ]
    }
  ],
  "timestamp": "2026-04-23T18:30:00+00:00"
}
```

## MS-001 Fiscal ACBr

### `POST /api/v1/nfe/emitir`

Emite NF-e.

```bash
curl -s \
  -H "Content-Type: application/json" \
  -d '{
    "vale_id": 123,
    "tipo": "NFe",
    "correlation_id": "vale-123",
    "customer": {"name": "John Doe", "doc": "12345678901"},
    "items": [{"sku": "BAT-001", "price": 500.00}]
  }' \
  http://localhost:8001/api/v1/nfe/emitir
```

Resposta de sucesso exemplo:

```json
{
  "status": "authorized",
  "correlation_id": "vale-123",
  "document_number": "123456",
  "access_key": "35260412345678000123550010000012345678901234"
}
```

Resposta de contingencia exemplo:

```json
{
  "status": "contingency",
  "correlation_id": "vale-123",
  "message": "Documento enviado para fila de contingencia"
}
```

### `GET /api/v1/contingencia/fila`

```bash
curl -s http://localhost:8001/api/v1/contingencia/fila
```

Resposta exemplo:

```json
{
  "data": [
    {
      "id": 1,
      "nota_id": 10,
      "status": "pending"
    }
  ]
}
```

## MS-002 Bancario

### `POST /api/v1/pix`

Cria cobranca PIX.

```bash
curl -s \
  -H "Content-Type: application/json" \
  -d '{
    "idempotency_key": "11111111-2222-3333-4444-555555555555",
    "erp_fatura_id": 3003,
    "banco_id": 1,
    "valor": 99.90
  }' \
  http://localhost:8002/api/v1/pix
```

Resposta exemplo:

```json
{
  "id": 55,
  "status": "pendente",
  "qrcode_pix": "000201010212...",
  "link_pagamento": "https://pagamentos.exemplo/pix/55"
}
```

### `POST /api/v1/boleto`

```bash
curl -s \
  -H "Content-Type: application/json" \
  -d '{
    "idempotency_key": "aaaa1111-bbbb-2222-cccc-333333333333",
    "erp_fatura_id": 1001,
    "banco_id": 1,
    "valor": 150.50,
    "vencimento": "2026-04-28",
    "sacado": {
      "nome": "Teste Sacado",
      "documento": "000.000.000-00"
    }
  }' \
  http://localhost:8002/api/v1/boleto
```

Resposta exemplo:

```json
{
  "id": 12,
  "status": "pendente",
  "tipo": "boleto",
  "nosso_numero": "0000123456",
  "linha_digitavel": "34191.79001 01043.510047 91020.150008 8 10470000015050"
}
```

### `POST /api/v1/webhook/pixbank`

```bash
curl -s \
  -H "Content-Type: application/json" \
  -d '{
    "txid": "MY-TXID-123",
    "valor_pago": 100.00,
    "status": "pago"
  }' \
  http://localhost:8002/api/v1/webhook/pixbank
```

Resposta exemplo:

```json
{
  "status": "updated"
}
```

## MS-003 WhatsApp n8n

### `POST /api/v1/notificacao/enviar`

```bash
curl -s \
  -H "Content-Type: application/json" \
  -d '{
    "to": "5511888888888",
    "message": "Seu pedido saiu!",
    "evento": "ENTREGA_SAIU"
  }' \
  http://localhost:8003/api/v1/notificacao/enviar
```

Resposta exemplo em horario comercial:

```json
{
  "status": "queued",
  "channel": "whatsapp"
}
```

Resposta exemplo fora do horario:

```json
{
  "status": "scheduled",
  "channel": "whatsapp"
}
```

Resposta exemplo para numero bloqueado:

```json
{
  "status": "blocked",
  "reason": "blacklist"
}
```

### `POST /api/v1/webhook/evolution`

```bash
curl -s \
  -H "Content-Type: application/json" \
  -d '{
    "data": {
      "key": {"remoteJid": "5511777777777@s.whatsapp.net"},
      "message": {"conversation": "PARAR"}
    }
  }' \
  http://localhost:8003/api/v1/webhook/evolution
```

Resposta exemplo:

```json
{
  "status": "received"
}
```

## MS-004 Open Finance

### `GET /api/v1/consentimentos`

```bash
curl -s http://localhost:8004/api/v1/consentimentos
```

Resposta exemplo:

```json
{
  "data": [
    {
      "id": 1,
      "empresa_id": 1,
      "status": "ativo",
      "provider": {
        "id": 1,
        "nome": "Banco X",
        "provider": "mock"
      }
    }
  ]
}
```

### `POST /api/v1/extratos/capturar/{consentimentoId}`

```bash
curl -s \
  -H "Content-Type: application/json" \
  -d '{
    "data_inicio": "2026-04-01",
    "data_fim": "2026-04-20"
  }' \
  http://localhost:8004/api/v1/extratos/capturar/1
```

Resposta exemplo:

```json
{
  "status": "accepted",
  "consentimento_id": 1
}
```

### `GET /api/v1/transacoes`

```bash
curl -s http://localhost:8004/api/v1/transacoes
```

Resposta exemplo:

```json
{
  "data": [
    {
      "id": 10,
      "descricao": "PAGTO LUZ",
      "valor": -150,
      "tipo": "debito",
      "data_lancamento": "2026-04-18"
    }
  ]
}
```

## MS-005 Geocoding

### `POST /api/v1/geocodificar`

```bash
curl -s \
  -H "Content-Type: application/json" \
  -d '{"endereco":"Rua A, 10 - Sao Paulo/SP"}' \
  http://localhost:8005/api/v1/geocodificar
```

Resposta exemplo:

```json
{
  "status": "ok",
  "latitude": -23.5505,
  "longitude": -46.6333,
  "confidence": "high"
}
```

### `POST /api/v1/rotas/otimizar`

```bash
curl -s \
  -H "Content-Type: application/json" \
  -d '{
    "tenant_id_externo": "tenant-001",
    "base_operacional_id": "base-sp-centro",
    "base_lat": -23.55,
    "base_lng": -46.63,
    "data_entrega": "2026-04-23",
    "entregas": [
      {"id": 101, "endereco": "Rua A, 10", "cliente": "Joao"},
      {"id": 102, "endereco": "Av B, 20", "cliente": "Maria"}
    ]
  }' \
  http://localhost:8005/api/v1/rotas/otimizar
```

Resposta exemplo:

```json
{
  "status": "otimizada",
  "rota_id": 1,
  "paradas": [
    {"entrega_id": 101, "ordem": 1},
    {"entrega_id": 102, "ordem": 2}
  ]
}
```

### `POST /api/v1/eta/recalcular`

```bash
curl -s \
  -H "Content-Type: application/json" \
  -d '{
    "rota_id": 1,
    "localizacao_atual": {"lat": -23.56, "lng": -46.64}
  }' \
  http://localhost:8005/api/v1/eta/recalcular
```

Resposta exemplo:

```json
{
  "status": "recalculated",
  "eta_minutos": 35
}
```

## Healthchecks

```bash
curl -s http://localhost:8000/up
curl -s http://localhost:8001/api/v1/health
curl -s http://localhost:8002/api/v1/health
curl -s http://localhost:8003/api/v1/health
curl -s http://localhost:8004/api/v1/health
curl -s http://localhost:8005/api/v1/health
```

Ou execute:

```bash
./healthcheck.sh
```

## Referencias

- [MICROSERVICES.md](./MICROSERVICES.md)
- [openapi.yaml](./openapi.yaml)
- [postman_collection.json](./postman_collection.json)
