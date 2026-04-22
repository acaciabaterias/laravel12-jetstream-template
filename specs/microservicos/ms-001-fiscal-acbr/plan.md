# Implementation Plan: MS-001 — Fiscal (SEFAZ) via ACBr

**Identificador**: `MS-001-FISCAL-ACBR`
**Spec**: [spec.md](spec.md)
**Repositório**: `ms-001-fiscal-acbr` (projeto separado do ERP)

---

## Constitution Check

> Requisito da constitution v2.0.0 — Quality Gate 1 e 2: *"Every implementation plan MUST include a Constitution Check. Constitution check gates in planning MUST pass before implementation begins."*

| Functional Requirement | Princípio da Constitution | Status | Notas |
|---|---|---|---|
| FR-001-01: Emissão de NF-e | **VI. Integrated Fiscal Compliance** — "The system MUST communicate with dedicated microservices for issuance of NF-e" | ✅ Alinhado | Este MS é exatamente o microserviço dedicado mandatado pelo Princípio VI |
| FR-001-02: Emissão de NFC-e (PDV) | **VI. Integrated Fiscal Compliance** — "Fiscal Coupons (PDV)" | ✅ Alinhado | NFC-e cobre o Cupom Fiscal do PDV |
| FR-001-03: Modo Contingência Automático | **VI. Integrated Fiscal Compliance** — operação ininterrupta | ✅ Alinhado | Garante que o balconista (Princípio I — domínio de revenda) nunca trave por SEFAZ fora do ar |
| FR-001-04: Cancelamento de NF-e | **VI. Integrated Fiscal Compliance** — "Users MUST be able to consult, cancel documents directly through ERP" | ✅ Alinhado | |
| FR-001-05: Carta de Correção (CC-e) | **VI. Integrated Fiscal Compliance** — "correct… fiscal documents directly through ERP" | ✅ Alinhado | |
| FR-001-06: Monitoramento de Certificado | **VI. Integrated Fiscal Compliance** — ensures continued fiscal compliance | ✅ Alinhado | Prevenção de interrupção operacional no domínio de revenda (Princípio I) |
| FR-001-07: Inutilização de Numeração | **VI. Integrated Fiscal Compliance** — conformidade fiscal plena | ✅ Alinhado | |

**Princípios sem conflito identificado:** I, II, III, IV, V — não impactados por este MS.

**Stack Tecnológica (Quality Gate — Technology Stack Constraints):**
- Node.js 20+ (Fastify): ✅ Não conflita com o stack Laravel 12 do ERP principal (MS é serviço separado com justificativa de performance I/O)
- ACBr Docker: ✅ Mandatado implicitamente como "dedicated microservices for fiscal automation"
- PostgreSQL: ✅ Stack canônico
- Redis: ✅ Stack canônico (Laravel Horizon equivalente no contexto do MS)

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
    attempts: 10,
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
