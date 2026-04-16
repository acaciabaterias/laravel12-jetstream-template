# Implementation Plan: MS-001 — Fiscal (SEFAZ) via ACBr

**Identificador**: `MS-001-FISCAL-ACBR`
**Spec**: [spec.md](spec.md)
**Repositório**: `ms-001-fiscal-acbr` (projeto separado do ERP)

---

## Stack Tecnológica

| Camada | Tecnologia | Justificativa |
|---|---|---|
| **API / Worker** | Node.js 20+ (Fastify) | Alta performance I/O, ecossistema robusto de filas |
| **Motor Fiscal** | ACBr via container Docker | Solução consolidada, evita reimplementar regras SEFAZ |
| **Banco de Dados** | PostgreSQL 15+ | Apenas para log/audit trail das notas |
| **Fila/Broker** | Redis (BullMQ) | Gerenciamento de contingência e retry exponencial |
| **Storage DANFE** | MinIO ou S3-compatible | Armazenamento de PDFs e XMLs |
| **Containerização** | Docker + Docker Compose | Isolamento ACBr + API + Redis |

---

## Padrão de Comunicação

```
ERP (Módulo 009)
    └── Redis Broker
            ├── PUBLICA: VALE_FATURADO  →  MS-001 (consumer)
            ├── PUBLICA: NF_CANCELAR    →  MS-001 (consumer)
            └── MS-001 PUBLICA:
                    ├── NF_AUTORIZADA
                    ├── NF_EM_CONTINGENCIA
                    ├── NF_CANCELADA
                    └── NF_ERRO
```

**Protocolo REST** (chamada direta pelo MS-001 → ACBr container via HTTP/TCP local):
```
MS-001 API (Node.js) → ACBr Server (container) → SEFAZ WebService (SOAP)
```

---

## Configuração ACBr

### Imagem Docker

```yaml
# docker-compose.yml
services:
  acbr-server:
    image: acbr/acbr-server:latest  # ou build customizado
    # Alternativa: https://hub.docker.com/r/acbr/acbrmonitor
    volumes:
      - ./certs:/app/certs          # Certificados A1
      - ./config/acbr.ini:/app/acbr.ini
    ports:
      - "9000:9000"                 # ACBr Server TCP/HTTP
    environment:
      ACBR_AMBIENTE: "2"            # 1=Produção, 2=Homologação
      ACBR_UF: "SP"                 # Estado do emitente
    restart: unless-stopped

  ms-fiscal-api:
    build: ./api
    environment:
      ACBR_URL: "http://acbr-server:9000"
      DATABASE_URL: "postgresql://..."
      REDIS_URL: "redis://redis:6379"
    depends_on:
      - acbr-server
      - redis

  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data
```

### Configuração do Certificado A1

```ini
; config/acbr.ini
[Certificado]
Tipo=A1
Arquivo=/app/certs/certificado.pfx
Senha=SENHA_SEGURA_VIA_ENV

[NFe]
Ambiente=2
UF=SP
Versao=4.00
```

**Rotação de Certificado**: O arquivo `.pfx` deve ser montado via Volume Secret (Docker Swarm/Kubernetes) ou variável de ambiente base64-encoded. O MS verifica a validade ao iniciar e a cada 24h via cron interno.

---

## Estrutura de Pastas

```
ms-001-fiscal-acbr/
├── api/                          # Node.js (Fastify)
│   ├── src/
│   │   ├── routes/
│   │   │   ├── nfe.routes.ts     # POST /nfe/emitir, /nfe/cancelar, etc.
│   │   │   └── health.routes.ts
│   │   ├── services/
│   │   │   ├── AcbrService.ts    # Comunicação com ACBr container
│   │   │   ├── EmissaoService.ts # Orquestra emissão + contingência
│   │   │   └── CertificadoService.ts
│   │   ├── consumers/
│   │   │   ├── ValeFaturadoConsumer.ts
│   │   │   └── NfCancelarConsumer.ts
│   │   ├── queues/
│   │   │   └── ContingenciaQueue.ts  # BullMQ
│   │   ├── models/
│   │   │   └── NotaFiscalJob.ts  # Entidade PostgreSQL via Prisma
│   │   └── server.ts
│   ├── package.json
│   └── Dockerfile
├── acbr-wrapper/
│   └── acbr-client.ts            # Client HTTP/TCP para ACBr Server
├── config/
│   └── acbr.ini.template
├── certs/                        # .gitignored — montar via Docker Volume
├── docker-compose.yml
├── docker-compose.prod.yml
└── .env.example
```

---

## Retry / Exponential Backoff

```typescript
// queues/ContingenciaQueue.ts
const RETRY_DELAYS_MS = [
  60_000,       // 1 minuto
  300_000,      // 5 minutos
  1_800_000,    // 30 minutos
  7_200_000,    // 2 horas
  21_600_000,   // 6 horas
];

const contingenciaQueue = new Queue('contingencia-nfe', {
  defaultJobOptions: {
    attempts: 5,
    backoff: {
      type: 'custom',
      delay: (attemptsMade) => RETRY_DELAYS_MS[attemptsMade] ?? 21_600_000,
    },
    removeOnComplete: true,
    removeOnFail: false,
  },
});
```

**Regra de limite absoluto**: Se uma nota falhar por > 10 tentativas ou > 24 horas na fila, publicar evento `NF_CONTINGENCIA_CRITICA` e notificar via webhook de alerta (Slack/e-mail).

---

## Monitoramento e Alertas

| Métrica | Ferramenta | Threshold de Alerta |
|---|---|---|
| Tamanho da fila de contingência | Prometheus + Grafana | > 10 notas pendentes |
| Tempo de resposta SEFAZ | Prometheus histogram | p95 > 3s |
| Taxa de rejeição de notas | Counter Prometheus | > 1% em 1h |
| Validade do certificado | Cron diário | ≤ 30 dias para expirar |
| Health do ACBr container | Docker healthcheck | Container restart |

```yaml
# prometheus scrape config (inserir no prometheus.yml)
- job_name: 'ms-fiscal'
  static_configs:
    - targets: ['ms-fiscal-api:3000']
  metrics_path: '/metrics'
```

---

## Segurança

- Certificado A1 NUNCA deve estar no repositório (`.gitignore` obrigatório para `/certs/*.pfx`)
- Comunicação interna ACBr ↔ API via rede Docker privada (não exposta externamente)
- API Gateway autentica requisições do ERP com JWT ou API Key compartilhada
- Secrets gerenciados via Docker Secrets ou HashiCorp Vault
