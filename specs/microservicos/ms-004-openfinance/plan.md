# Implementation Plan: MS-004 — Open Finance (Extratos e Conciliação)

**Identificador**: `MS-004-OPENFINANCE`
**Spec**: [spec.md](spec.md)
**Repositório**: `ms-004-openfinance` (projeto separado do ERP)

---

## Stack Tecnológica

| Camada | Tecnologia | Justificativa |
|---|---|---|
| **API / Worker** | Python 3.11+ (FastAPI) | Excelente para I/O async, bibliotecas OAuth consolidadas |
| **Banco de Dados** | PostgreSQL 15+ | Persistência de consentimentos, transações e logs |
| **Cache** | Redis 7+ | Deduplicação e controle de concorrência (locks) |
| **Scheduler (Cron)** | APScheduler (Python) ou Celery Beat | Cron job a cada 4 horas cross-instância |
| **Broker** | Redis Pub/Sub ou RabbitMQ | Publicação de eventos para o Módulo 008 |
| **ORM** | SQLAlchemy 2.0 + Alembic | Migrations e acesso ao banco |
| **HTTP Client** | httpx (async) | Comunicação com APIs dos providers |
| **Criptografia** | `cryptography` lib (Python) | AES-256-GCM para tokens OAuth |

---

## Padrão de Comunicação

```
Usuário (via Módulo 008 / ERP)
    └── MS-004 /oauth/authorize  →  Banco (OAuth flow)
            └── Callback → MS-004 (salva consentimento + token)

Cron Job (cada 4h)
    └── MS-004 CapturaService
            ├── Pluggy API / Belvo API / Open Finance Direto
            └── Redis Broker → TRANSACOES_CAPTURADAS → Módulo 008

Módulo 008
    └── Redis Broker → EXTRATO_CAPTURAR_MANUAL → MS-004 (on-demand)
```

---

## Integração com Providers

### Adapter Pattern

```python
from abc import ABC, abstractmethod

class FinanceProviderAdapter(ABC):
    @abstractmethod
    async def get_transactions(
        self, consentimento: Consentimento, since: datetime
    ) -> list[TransacaoNormalizada]:
        ...

    @abstractmethod
    async def refresh_token(self, consentimento: Consentimento) -> TokenData:
        ...
```

**Adapters implementados:**
- `PluggyAdapter` — Pluggy API (conecta via item_id do Pluggy)
- `BelvoAdapter` — Belvo API (conecta via link_id do Belvo)
- `OpenFinanceBrasilAdapter` — API direta Open Finance Brasil (complexa, prioridade 2)

### Prioridade de Implementação

1. **v1**: Pluggy (cobre 90% dos bancos brasileiros, OAuth simplificado via Pluggy Connect widget)
2. **v1**: Belvo (fallback para bancos não cobertos pelo Pluggy)
3. **v2**: Open Finance Brasil direto (para grandes empresas que exigem conformidade total)

---

## Estrutura de Pastas

```
ms-004-openfinance/
├── app/
│   ├── api/
│   │   ├── routes/
│   │   │   ├── oauth.py          # /oauth/authorize, /oauth/callback
│   │   │   ├── consentimentos.py
│   │   │   ├── extratos.py
│   │   │   └── health.py
│   │   └── dependencies.py
│   ├── adapters/
│   │   ├── base.py               # FinanceProviderAdapter (ABC)
│   │   ├── pluggy.py
│   │   ├── belvo.py
│   │   └── openfinance_brasil.py
│   ├── services/
│   │   ├── ConsentimentoService.py
│   │   ├── CapturaService.py     # Orquestra captura + deduplicação
│   │   ├── NormalizacaoService.py
│   │   └── PublicadorService.py  # Publica eventos no broker
│   ├── scheduler/
│   │   └── cron.py               # APScheduler — job a cada 4h
│   ├── consumers/
│   │   └── ExtratoManualConsumer.py
│   ├── models/
│   │   └── (SQLAlchemy models)
│   └── main.py
├── alembic/
│   └── versions/
├── tests/
├── docker-compose.yml
├── requirements.txt
└── .env.example
```

---

## Configuração do Cron Job

```python
# scheduler/cron.py
from apscheduler.schedulers.asyncio import AsyncIOScheduler

scheduler = AsyncIOScheduler()

@scheduler.scheduled_job('cron', hour='0,4,8,12,16,20', minute=0)
async def capturar_todos_consentimentos():
    consentimentos = await ConsentimentoService.listar_ativos()
    tasks = [CapturaService.capturar(c) for c in consentimentos]
    await asyncio.gather(*tasks, return_exceptions=True)
    # Cada task falha independentemente — uma não afeta as outras
```

---

## Retry / Falhas de Provider

```python
# Retry com tenacity (Python)
from tenacity import retry, stop_after_attempt, wait_exponential

@retry(
    stop=stop_after_attempt(3),
    wait=wait_exponential(multiplier=1, min=30, max=600),
    reraise=True,
)
async def buscar_transacoes_com_retry(adapter, consentimento, since):
    return await adapter.get_transactions(consentimento, since)
```

---

## Segurança

- Access tokens e refresh tokens SEMPRE armazenados criptografados (AES-256-GCM, chave via env `ENCRYPTION_KEY`)
- Tokens NUNCA aparecem em logs (middleware de sanitização)
- Consentimentos isolados por `empresa_id` (multi-tenant)
- Rate limiting na API (100 req/min por empresa_id)

---

## Monitoramento e Alertas

| Métrica | Threshold |
|---|---|
| Consentimentos expirados sem renovação | > 0 por mais de 24h |
| Taxa de falha do cron job | > 20% dos consentimentos falhando |
| Transações capturadas por hora | Queda brusca (< 50% da média) |
| Latência de captura por provider | p95 > 30s |
| Duplicatas descartadas | > 10% do total (possível problema no parser) |
