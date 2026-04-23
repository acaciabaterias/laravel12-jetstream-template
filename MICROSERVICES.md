# Microservices Guide

## Visao Geral

O monorepo possui um ERP Core em Laravel 12 e cinco microservicos especializados.

Ponto importante de roteamento:

- acesso direto ao microservico: `/api/v1/...`
- acesso via gateway ou reverse proxy, conforme `openapi.yaml`: `/ms-00X/v1/...`

Nos exemplos abaixo, o formato principal usado e o acesso direto, porque ele reflete as rotas presentes em `routes/api.php` de cada microservico e os testes automatizados do repositorio.

## Mapa Rapido

| Servico | Porta local | Responsabilidade principal | Healthcheck |
|---|---:|---|---|
| ERP Core | `8000` | Backoffice, autenticacao, tenant resolution, UI e APIs centrais | `GET /up` |
| MS-001 Fiscal ACBr | `8001` | emissao fiscal, cancelamento, contingencia e certificado | `GET /api/v1/health` |
| MS-002 Bancario | `8002` | boleto, PIX, CNAB, webhooks bancarios | `GET /api/v1/health` |
| MS-003 WhatsApp n8n | `8003` | notificacoes, fila, blacklist e webhooks | `GET /api/v1/health` |
| MS-004 Open Finance | `8004` | OAuth bancario, consentimentos e captura de extratos | `GET /api/v1/health` |
| MS-005 Geocoding | `8005` | geocodificacao, rotas, ETA e localizacao | `GET /api/v1/health` |

## ERP Core

### Responsabilidade

- autenticacao com Jetstream, Fortify e Sanctum
- resolucao do tenant e troca de conexao
- backoffice administrativo
- modulos de estoque, vendas, logistica, garantias e financeiro
- orquestracao para os microservicos

### Endpoints principais

- `GET /up`
- `GET /api/user`
- `GET /api/sync/mobile`

### Como testar

Smoke test:

```bash
curl -i http://localhost:8000/up
```

API autenticada:

```bash
curl -i \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Accept: application/json" \
  http://localhost:8000/api/user
```

Sincronizacao mobile:

```bash
curl -i \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Accept: application/json" \
  http://localhost:8000/api/sync/mobile
```

## MS-001 Fiscal ACBr

### Responsabilidade

- emissao de NF-e e NFC-e
- cancelamento e carta de correcao
- inutilizacao de numeracao
- consulta por chave de acesso
- fila de contingencia quando o driver fiscal falha

### Endpoints principais

- `POST /api/v1/nfe/emitir`
- `POST /api/v1/nfce/emitir`
- `POST /api/v1/nfe/cancelar`
- `POST /api/v1/nfe/cce`
- `POST /api/v1/nfe/inutilizar`
- `GET /api/v1/nfe/{chaveAcesso}`
- `GET /api/v1/contingencia/fila`
- `GET /api/v1/certificado/status`
- `GET /api/v1/health`

### Como testar

Health:

```bash
curl -i http://localhost:8001/api/v1/health
```

Emissao mock:

```bash
curl -i \
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

Fila de contingencia:

```bash
curl -i http://localhost:8001/api/v1/contingencia/fila
```

## MS-002 Bancario

### Responsabilidade

- emissao de boleto
- emissao de cobranca PIX
- consulta e cancelamento de cobrancas
- geracao de remessa CNAB
- processamento de retorno CNAB
- recebimento de webhooks bancarios

### Endpoints principais

- `POST /api/v1/boleto`
- `POST /api/v1/pix`
- `GET /api/v1/cobranca/{id}`
- `DELETE /api/v1/cobranca/{id}`
- `POST /api/v1/cnab/remessa`
- `POST /api/v1/cnab/retorno`
- `POST /api/v1/webhook/{banco}`
- `GET /api/v1/bancos`
- `GET /api/v1/health`

### Como testar

Health:

```bash
curl -i http://localhost:8002/api/v1/health
```

PIX:

```bash
curl -i \
  -H "Content-Type: application/json" \
  -d '{
    "idempotency_key": "11111111-2222-3333-4444-555555555555",
    "erp_fatura_id": 3003,
    "banco_id": 1,
    "valor": 99.90
  }' \
  http://localhost:8002/api/v1/pix
```

Webhook:

```bash
curl -i \
  -H "Content-Type: application/json" \
  -d '{
    "txid": "MY-TXID-123",
    "valor_pago": 100.00,
    "status": "pago"
  }' \
  http://localhost:8002/api/v1/webhook/pixbank
```

## MS-003 WhatsApp n8n

### Responsabilidade

- envio de notificacoes transacionais
- agendamento fora do horario comercial
- historico de execucoes
- blacklist de contatos
- integracao com Evolution API e n8n

### Endpoints principais

- `POST /api/v1/notificacao/enviar`
- `GET /api/v1/notificacao/historico/{clienteId}`
- `GET /api/v1/fila`
- `GET /api/v1/blacklist`
- `POST /api/v1/blacklist`
- `DELETE /api/v1/blacklist/{numero}`
- `POST /api/v1/webhook/evolution`
- `POST /api/v1/webhook/erp/{evento}`
- `GET /api/v1/health`

### Como testar

Health:

```bash
curl -i http://localhost:8003/api/v1/health
```

Envio:

```bash
curl -i \
  -H "Content-Type: application/json" \
  -d '{
    "to": "5511888888888",
    "message": "Seu pedido saiu!",
    "evento": "ENTREGA_SAIU"
  }' \
  http://localhost:8003/api/v1/notificacao/enviar
```

Blacklist:

```bash
curl -i \
  -H "Content-Type: application/json" \
  -d '{
    "numero": "5511999999999",
    "motivo": "Opt-out"
  }' \
  http://localhost:8003/api/v1/blacklist
```

## MS-004 Open Finance

### Responsabilidade

- iniciar OAuth com bancos e provedores
- persistir consentimentos
- capturar extratos e transacoes
- deduplicar transacoes por hash
- manter trilha de logs de captura

### Endpoints principais

- `GET /api/v1/oauth/authorize/{banco}`
- `GET /api/v1/oauth/callback`
- `GET /api/v1/consentimentos`
- `DELETE /api/v1/consentimentos/{id}`
- `POST /api/v1/extratos/capturar/{consentimentoId}`
- `GET /api/v1/transacoes`
- `GET /api/v1/captura/logs`
- `GET /api/v1/health`

### Como testar

Health:

```bash
curl -i http://localhost:8004/api/v1/health
```

Listagem:

```bash
curl -i http://localhost:8004/api/v1/consentimentos
```

Captura:

```bash
curl -i \
  -H "Content-Type: application/json" \
  -d '{
    "data_inicio": "2026-04-01",
    "data_fim": "2026-04-20"
  }' \
  http://localhost:8004/api/v1/extratos/capturar/1
```

## MS-005 Geocoding

### Responsabilidade

- geocodificar enderecos
- corrigir coordenadas manualmente
- otimizar rotas
- recalcular ETA
- armazenar localizacao de entregadores
- invalidar cache de geocodificacao

### Endpoints principais

- `POST /api/v1/geocodificar`
- `PUT /api/v1/geocodificar/corrigir`
- `POST /api/v1/rotas/otimizar`
- `GET /api/v1/rotas/{id}`
- `POST /api/v1/localizacao`
- `GET /api/v1/localizacao/{entregadorId}`
- `POST /api/v1/eta/recalcular`
- `DELETE /api/v1/cache/geocodificacao/{hash}`
- `GET /api/v1/health`

### Como testar

Health:

```bash
curl -i http://localhost:8005/api/v1/health
```

Geocodificacao:

```bash
curl -i \
  -H "Content-Type: application/json" \
  -d '{"endereco":"Rua A, 10 - Sao Paulo/SP"}' \
  http://localhost:8005/api/v1/geocodificar
```

Otimizacao de rota:

```bash
curl -i \
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

## Teste Integrado do Stack

Para validar o ambiente completo:

```bash
docker compose up -d --build
./healthcheck.sh
```

## Referencias

- [README.md](./README.md)
- [openapi.yaml](./openapi.yaml)
- [postman_collection.json](./postman_collection.json)
- [ARCHITECTURE.md](./ARCHITECTURE.md)
