# Implementation Plan: MS-002 — Bancário (Boletos, PIX e CNAB)

**Identificador**: `MS-002-BANCARIO`
**Spec**: [spec.md](spec.md)
**Repositório**: `ms-002-bancario` (projeto separado do ERP)

---

## Stack Tecnológica

| Camada | Tecnologia | Justificativa |
|---|---|---|
| **API / Worker** | Node.js 20+ (Fastify) | Alta performance para I/O intensivo com APIs bancárias |
| **Banco de Dados** | PostgreSQL 15+ | Persistência de cobranças, CNAB logs e audit trail |
| **Broker** | Redis (BullMQ) | Filas de geração de cobranças e processamento CNAB |
| **Parser CNAB** | `node-cnab` ou `cnab-parser` | Biblioteca consolidada para parsing CNAB 240/400 |
| **HTTP Client** | Axios + interceptors | Comunicação com APIs bancárias com retry automático |
| **Criptografia** | Node.js `crypto` + AES-256 | Criptografia de credenciais bancárias em repouso |

---

## Padrão de Comunicação

```
ERP (Módulo 009)
    └── Redis Broker
            ├── PUBLICA: COBRANCA_CRIAR_BOLETO  →  MS-002
            ├── PUBLICA: COBRANCA_CRIAR_PIX      →  MS-002
            ├── PUBLICA: CNAB_RET_PROCESSAR      →  MS-002
            └── MS-002 PUBLICA:
                    ├── COBRANCA_CRIADA
                    ├── COBRANCA_PAGA
                    ├── COBRANCA_EXPIRADA
                    └── CNAB_RET_PROCESSADO

Bancos (externos)
    └── Webhook PUT/POST → MS-002 /api/v1/webhook/{banco}
```

---

## Integração com Bancos

### Adapters por Banco

Cada banco terá seu próprio `Adapter` implementando uma interface comum:

```typescript
interface BancoAdapter {
  gerarBoleto(cobranca: CobrancaPayload): Promise<BoletoResult>;
  gerarPix(cobranca: CobrancaPayload): Promise<PixResult>;
  cancelar(nossoNumero: string): Promise<void>;
  consultarStatus(nossoNumero: string): Promise<CobrancaStatus>;
  gerarCnabRemessa(cobranças: Cobranca[]): Promise<string>;
  parsearCnabRetorno(arquivo: string): Promise<RetornoItem[]>;
}
```

**Bancos suportados na v1:**
- Bradesco (API Bradesco Developer)
- Itaú (API Itaú e-Commerce)
- Sicoob (API Sicoob PIX + CNAB)
- Banco do Brasil (API BB Developers)
- Caixa Econômica Federal (API CEF + CNAB 240)

---

## Estrutura de Pastas

```
ms-002-bancario/
├── src/
│   ├── routes/
│   │   ├── boleto.routes.ts
│   │   ├── pix.routes.ts
│   │   ├── cnab.routes.ts
│   │   └── webhook.routes.ts
│   ├── adapters/
│   │   ├── BancoAdapter.interface.ts
│   │   ├── BradescoAdapter.ts
│   │   ├── ItauAdapter.ts
│   │   ├── SicoobAdapter.ts
│   │   ├── BancoDoBrasilAdapter.ts
│   │   └── CaixaAdapter.ts
│   ├── services/
│   │   ├── CobrancaService.ts       # Orquestra geração com idempotência
│   │   ├── CnabService.ts           # Parser + gerador CNAB
│   │   └── WebhookService.ts        # Valida + processa webhooks bancários
│   ├── consumers/
│   │   ├── CobrancaCriarConsumer.ts
│   │   └── CnabRetConsumer.ts
│   ├── queues/
│   │   └── CobrancaQueue.ts         # BullMQ retry strategy
│   ├── models/                      # Prisma models
│   └── server.ts
├── prisma/
│   └── schema.prisma
├── docker-compose.yml
└── .env.example
```

---

## Configuração de Retry / Falhas Bancárias

```typescript
// Retry para falhas de API bancária (503, network errors)
const RETRY_DELAYS_BANCO = [
  30_000,   // 30 segundos
  120_000,  // 2 minutos
  600_000,  // 10 minutos
];

// BullMQ job options
const jobOptions = {
  attempts: 3,
  backoff: { type: 'custom', delay: (n) => RETRY_DELAYS_BANCO[n] ?? 600_000 },
};
```

**Erros de negócio** (ex: dados inválidos retornados pelo banco) NÃO devem ser retentados — publicar `COBRANCA_ERRO` imediatamente.

---

## Segurança de Credenciais Bancárias

- Credenciais de cada banco (`client_id`, `client_secret`, certificados) são armazenadas criptografadas no PostgreSQL (coluna `credenciais_json_encrypted` — AES-256-GCM)
- Chave de criptografia via variável de ambiente `ENCRYPTION_KEY` (nunca no código)
- Webhooks autenticados por HMAC-SHA256 (chave configurada por banco)

---

## Monitoramento e Alertas

| Métrica | Threshold |
|---|---|
| Taxa de falha de geração de boleto | > 5% em 15 minutos |
| Tempo de resposta API bancária | p95 > 5s |
| Fila de cobranças pendentes | > 50 jobs |
| Webhooks não autenticados (possível ataque) | > 10 em 1 minuto |
| Cobranças expiradas sem pagamento (D+3) | Relatório diário |
