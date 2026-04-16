# Master Specification: ERP Baterias

## Estrutura Atualizada do Projeto

Abaixo estão os módulos planejados e especificados para o ERP BateriaExpert, priorizados por ordem de implementação e fundação arquitetural.

### NÍVEL 1: FUNDAÇÃO
1. **001: Multi-Filial / Tenant** - Isolamento de dados, Seletor de filiais, Escopos Globais. *Dependências: Nenhuma.*
2. **002: Usuários e Perfis / RBAC** - Controle de Autenticação, Papéis, Permissões e Auditoria de Acesso. *Dependências: Multi-Filial.*
3. **003: Cadastros Estruturais** - Veículos, Fabricantes, Baterias (Produtos) e Aplicações. *Dependências: Usuários, Filial.*

### NÍVEL 2: BASE OPERACIONAL
4. **004: Estoque e Logística Reversa** - Entrada NFe automática, controle de depósitos físicos, "Conta Sucata" e verificação do "Shelf Life" das baterias. *Dependências: Cadastros.*
5. **005: Vendas e "Vales"** - Orçamentos dinâmicos, vales pré-venda e acréscimos automáticos ao preço caso de venda sem recolhimento da sucata. *Dependências: Estoque, Usuários.*
6. **006: Logística / App Entregador** - Rastreamento das entregas no balcão e aplicativo mobile para gestão da pesagem da sucata pós-venda. *Dependências: Vendas.*

### NÍVEL 3: PÓS-VENDA E SERVIÇOS
7. **007: Garantias e Feedback** - Gestão de assistências técnicas, análise do desgaste da bateria, controle de empréstimos ativos, e alertas automatizados via WhatsApp. *Dependências: Vendas, Estoque.*

### NÍVEL 4: FINANCEIRO E FISCAL
8. **008: Financeiro Inteligente** - Conciliação bancária 100% via API, integração com microsserviço de baixa de boletos e relatórios consolidados de Margem de Lucro Bruto real. *Dependências: Vendas, Garantias, Logística.*
9. **009: Módulo Fiscal** - Emissão e gestão automatizada de notas do PDV, Cupom fiscal e Notas completas (NF-e) por filial. *Dependências: Vendas, Garantias.*

---

## Integrações Externas (Microserviços)

Os módulos do ERP são complementados por **5 Microserviços autônomos**, cada um em seu próprio repositório, que se comunicam com o ERP de forma **assíncrona via Message Broker (Redis/RabbitMQ)**. O ERP nunca chama os microserviços diretamente — ele publica eventos e consome respostas, garantindo desacoplamento total.

### Arquitetura Orientada a Eventos

```
ERP (Laravel 12)
    └── Redis / RabbitMQ (Message Broker)
            ├── PUBLICA eventos  →  Microserviços (consumers)
            └── CONSOME eventos  ←  Microserviços (publishers)

API Gateway (ponto de entrada único para chamadas REST síncronas quando necessário)
    └── Roteia para o MS correto por prefixo de rota
```

**Princípios:**
- Cada MS tem seu próprio banco de dados (PostgreSQL isolado por serviço)
- Um MS nunca acessa diretamente o banco de dados de outro MS
- Falhas em um MS não derrubam o ERP (circuit breaker + fila de contingência)
- Todos os MS expõem `/health` e métricas Prometheus

---

### MS-001: Fiscal (SEFAZ) via ACBr
**Repositório**: `ms-001-fiscal-acbr` | **Spec**: [specs/microservicos/ms-001-fiscal-acbr/](microservicos/ms-001-fiscal-acbr/)

Wrapper inteligente ao redor do **ACBr** (Aplicação Comercial Brasileira) para emissão de NF-e e NFC-e junto à SEFAZ. O ACBr roda em container Docker separado e já resolve assinatura digital (A1/A3), validação de XML, geração de DANFE e contingência offline.

**Decisão arquitetural chave — ACBr em Docker:**
O ACBr é executado como um servidor TCP/HTTP em container isolado. A API Node.js do MS-001 se comunica com ele via rede Docker interna. Isso permite atualizar o ACBr independentemente da API, além de isolar a complexidade fiscal em um container bem testado pela comunidade.

**Eventos principais:** `VALE_FATURADO` → emite NF-e → `NF_AUTORIZADA` | Contingência automática com retry exponencial (1min → 5min → 30min → 2h → 6h).

**Stack:** Node.js 20+ (Fastify) + ACBr Docker + PostgreSQL (logs) + Redis (fila de contingência)

---

### MS-002: Bancário (Boletos, PIX e CNAB)
**Repositório**: `ms-002-bancario` | **Spec**: [specs/microservicos/ms-002-bancario/](microservicos/ms-002-bancario/)

Abstrai toda a comunicação com instituições bancárias para geração de boletos registrados, cobranças PIX e processamento de arquivos CNAB 240/400. Suporta Bradesco, Itaú, Sicoob, Banco do Brasil e Caixa via padrão de Adapter.

**Eventos principais:** `COBRANCA_CRIAR_BOLETO` → gera boleto → `COBRANCA_CRIADA` | Webhooks bancários → `COBRANCA_PAGA` | `CNAB_RET_PROCESSAR` → processa arquivo retorno → `CNAB_RET_PROCESSADO`.

**Stack:** Node.js 20+ (Fastify) + PostgreSQL + Redis (BullMQ)

---

### MS-003: WhatsApp & Notificações via n8n
**Repositório**: `ms-003-whatsapp-n8n` | **Spec**: [specs/microservicos/ms-003-whatsapp-n8n/](microservicos/ms-003-whatsapp-n8n/)

Hub central de comunicação automatizada com clientes via WhatsApp, construído sobre **n8n** (plataforma low-code self-hosted) integrado com **Evolution API** (transporte WhatsApp Business).

**Decisão arquitetural chave — n8n como motor de workflows:**
Templates de mensagem e regras de envio mudam frequentemente por decisão de negócio. O n8n permite que usuários não-técnicos alterem workflows sem deploy de código, reduzindo o ciclo de atualização de dias para minutos. Cada evento do ERP dispara um workflow visual no n8n que decide canal, template e horário de envio.

**Workflows principais:** Confirmação de compra, aviso de entrega, alerta de garantia, pesquisa NPS, alertas internos (certificado fiscal, contingência crítica).

**Stack:** n8n (self-hosted) + Evolution API v2 + PostgreSQL + Redis

---

### MS-004: Open Finance (Extratos e Conciliação)
**Repositório**: `ms-004-openfinance` | **Spec**: [specs/microservicos/ms-004-openfinance/](microservicos/ms-004-openfinance/)

Captura automatizada de extratos bancários via Open Finance Brasil e agregadores (Pluggy, Belvo). Alimenta o Módulo 008 com transações normalizadas e deduplicadas a cada 4 horas.

**Eventos principais:** Cron job (4h) → captura todos os consentimentos → `TRANSACOES_CAPTURADAS` → Módulo 008 executa conciliação automática.

**Stack:** Python 3.11+ (FastAPI) + PostgreSQL + Redis + APScheduler

---

### MS-005: Geocoding & Routing
**Repositório**: `ms-005-geocoding` | **Spec**: [specs/microservicos/ms-005-geocoding/](microservicos/ms-005-geocoding/)

Inteligência geográfica para o App do Entregador e Módulo 006. Converte endereços em coordenadas, otimiza rotas de entrega (TSP) e recalcula ETAs em tempo real conforme o entregador avança.

**Algoritmos:** Nearest Neighbor + 2-opt (N ≤ 15 paradas) | Google Routes API (N > 15 paradas) | K-means clustering (N > 50 paradas).

**Providers:** Google Maps API (primário) + OpenStreetMap/Nominatim (fallback gratuito). Cache de geocodificação com TTL 30 dias no Redis.

**Stack:** Node.js 20+ (Fastify) + PostgreSQL com **PostGIS** + Redis

---

### Mapa de Eventos (ERP ↔ Microserviços)

| Evento | Publicado por | Consumido por | Descrição |
|---|---|---|---|
| `VALE_FATURADO` | Módulo 005 / 009 | MS-001, MS-003 | Venda faturada — emitir NF-e e notificar cliente |
| `NF_AUTORIZADA` | MS-001 | Módulo 009 | NF-e autorizada com chave e DANFE |
| `NF_EM_CONTINGENCIA` | MS-001 | Módulo 009, MS-003 | SEFAZ offline — nota na fila |
| `NF_CONTINGENCIA_CRITICA` | MS-001 | Módulo 009, MS-003 | Contingência atingiu 10 falhas/24h — intervenção humana necessária |
| `NF_INUTILIZAR` | Módulo 009 | MS-001 | Solicita inutilização de faixa de numeração NF |
| `COBRANCA_CRIAR_BOLETO` | Módulo 009 | MS-002 | Solicitar geração de boleto |
| `COBRANCA_PAGA` | MS-002 | Módulo 008, MS-003 | Pagamento confirmado → baixar fatura |
| `TRANSACOES_CAPTURADAS` | MS-004 | Módulo 008 | Extrato bancário para conciliação |
| `ROTA_CRIADA` | Módulo 006 | MS-005 | Lista de entregas para otimização |
| `ROTA_OTIMIZADA` | MS-005 | Módulo 006, App | Rota com ordem e ETAs calculados |
| `LOCALIZACAO_ATUALIZADA` | App Entregador | MS-005 | Posição atual → recalcular ETAs |
| `ENTREGA_SAIU` | Módulo 006 | MS-003 | Notificar cliente que entregador saiu |
| `GARANTIA_VENCENDO` | Módulo 007 | MS-003 | Lembrete de garantia para o cliente |
| `CERTIFICADO_EXPIRANDO` | MS-001 | MS-003 | Alerta de certificado A1 para TI |
