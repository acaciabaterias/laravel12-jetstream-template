# BateriaExpert Architecture

## Visão Geral

O ERP BateriaExpert segue uma arquitetura Laravel monolítica para o core do ERP, com microserviços especializados para domínios externos como fiscal, bancário, notificações, Open Finance e geocoding.

## Componentes Principais

- `app/`: regras de negócio, Livewire, policies, jobs, eventos e integrações do ERP
- `database/migrations/central`: catálogo SaaS central, tenants, planos e billing
- `database/migrations/tenant`: schema canônico de cada banco operacional do ERP
- `microservicos/`: APIs desacopladas para integrações especializadas

## Multi-Tenancy

- O banco central mantém catálogo de clientes, credenciais e billing
- Cada tenant opera em banco físico isolado
- A resolução da conexão ativa acontece via `TenantConnectionMiddleware`
- O core do ERP não usa `filial_id` como mecanismo de isolamento

## Fluxo de Aplicação

1. O usuário acessa o domínio do tenant
2. O middleware resolve o tenant no catálogo central
3. A aplicação troca a conexão para o banco físico do tenant
4. O módulo solicitado executa regras, jobs e eventos localmente
5. Quando necessário, o core delega para microserviços externos

## Módulos Core

- `001`: tenant management e catálogo central
- `002`: autenticação e RBAC
- `003`: cadastros estruturais
- `004`: estoque e logística reversa
- `005`: vendas, vales e OS
- `006`: logística e entregas
- `007`: garantias e feedback
- `008`: financeiro inteligente
- `009`: orquestração fiscal e bancária
- `010`: backbone de integração, contratos canônicos, replay operacional e observabilidade
- `011`: control plane comercial central com planos, assinaturas, faturas SaaS, bloqueio e reativação
- `012`: payments control plane central com emissão externa, webhooks idempotentes, conciliação e exceções financeiras

## Integrações Externas

- `MS-001`: fiscal ACBr
- `MS-002`: bancário/CNAB
- `MS-003`: WhatsApp e workflows
- `MS-004`: Open Finance
- `MS-005`: geocoding e rotas

## Backbone de Integração

- contratos versionados em `contratos_evento`
- publicação confiável com `evento_outboxes`
- consumo idempotente com `evento_inboxes`
- rastreabilidade de entrega em `entregas_integracao`
- catálogo síncrono controlado em `endpoints_integracao`
- inspeção operacional via `/integration/backbone` e `/api/integration/inspections`

## Control Plane Comercial

- o banco central mantém `planos`, `assinaturas`, `faturas`, `politicas_inadimplencia` e `eventos_comerciais_assinante`
- o módulo `011` não grava estado comercial nos bancos tenant
- grace period, bloqueio e reativação atualizam o cadastro central do assinante e o `BillingAccessGuard`
- eventos comerciais (`ASSINATURA_ATIVADA`, `PLANO_ALTERADO`, `GRACE_PERIOD_INICIADO`, `ASSINANTE_BLOQUEADO`, `ASSINANTE_REATIVADO`, `ASSINATURA_CANCELADA`) são publicados no backbone `010`
- o painel central opera via Livewire em rotas administrativas de billing e suporta inspeção JSON em `/admin/billing/inspection`

## Platform Payments and Reconciliation

- o banco central mantém `gateways_cobranca_saas`, `cobrancas_saas_externas`, `retornos_pagamento_saas`, `conciliacoes_pagamento_saas` e `excecoes_conciliacao_saas`
- o módulo `012` emite cobranças externas sempre vinculadas a uma `FaturaSaaS` do módulo `011`
- webhooks e retornos são ingeridos com chave de idempotência e só liquidam a fatura quando o match é seguro
- divergências de referência ou valor abrem exceções operacionais centrais sem sobrescrever o histórico financeiro original
- replay manual de retornos usa job/comando dedicado, preserva o retorno original e registra auditoria explícita em `audit_logs`
- eventos financeiros (`COBRANCA_SAAS_LIQUIDADA`, `CONCILIACAO_SAAS_PENDENTE`) são publicados no backbone `010` em escopo central
- o painel central opera via Livewire em `/admin/payments`, suporta emissão em `/admin/payments/emitir` e inspeção JSON em `/admin/payments/inspection`

## Padrões Técnicos

- Laravel 12 como núcleo de aplicação
- Livewire para interfaces reativas
- Jobs enfileirados para fluxos assíncronos
- Events/Listeners para desacoplamento de domínio
- Policies e Gates para controle de acesso
- PostgreSQL/Supabase como referência de persistência

## Diagramas

### 1. Fluxo de Venda

```mermaid
flowchart LR
    A[Vale Aberto] --> B[Itens do Vale]
    B --> C[Reserva de Estoque]
    C --> D[Pedido de Venda / Faturamento]
    D --> E[Boleto Orquestrado]
    D --> F[Nota Fiscal Orquestrada]
    F --> G[MS-001 Fiscal ACBr]
    E --> H[MS-002 Bancario]

    C --> I{Sucata devolvida?}
    I -->|Sim| J[Preco liquido mantido]
    I -->|Nao| K[Acrescimo por tabela de peso]
    J --> D
    K --> D
```

### 2. Fluxo de Garantia

```mermaid
flowchart TD
    A[Cliente retorna com reclamacao] --> B[Ordem de Servico de Garantia]
    B --> C[Vinculo com Vale original]
    C --> D[Analise tecnica / Laudo]
    D --> E{Resultado}
    E -->|Procedente| F[Garantia aprovada]
    E -->|Improcedente| G[Garantia improcedente]
    F --> H[Indice de retorno atualizado]
    F --> I[Notificacao ao cliente]
    G --> J[Geracao de cobranca]
    G --> I
    J --> K[Boleto / CNAB]
    K --> L[MS-002 Bancario]
    I --> M[MS-003 WhatsApp n8n]
```

### 3. Fluxo de Logistica

```mermaid
flowchart LR
    A[Rota de Entrega] --> B[Pontos de Entrega]
    B --> C[Entrega em campo]
    C --> D[Registro de sucata]
    C --> E[Registro de recebimento]
    D --> F[Conta de Sucata]
    E --> G[Recebimento Movel]
    G --> H[Sincronizacao com ERP]
    F --> H
    H --> I[Baixa e fechamento operacional]

    A --> J[MS-005 Geocoding]
    J --> K[Otimizacao de rota / ETA]
    K --> A
```

### 4. Arquitetura dos Microservicos

```mermaid
flowchart TB
    U[Usuarios ERP / App Entregador] --> C[ERP Core Laravel 12]

    C --> DB[(Banco Central)]
    C --> T[(Banco Tenant)]
    C --> R[(Redis / Queues)]

    C --> MS1[MS-001 Fiscal ACBr]
    C --> MS2[MS-002 Bancario]
    C --> MS3[MS-003 WhatsApp n8n]
    C --> MS4[MS-004 Open Finance]
    C --> MS5[MS-005 Geocoding]

    MS1 --> X1[Emissao NF-e / NFC-e]
    MS2 --> X2[Boleto / PIX / CNAB]
    MS3 --> X3[Notificacoes / Webhooks]
    MS4 --> X4[OAuth / Extratos]
    MS5 --> X5[Geocoding / Rotas / ETA]

    MS1 --> R
    MS2 --> R
    MS3 --> R
    MS4 --> R
    MS5 --> R
```

### 5. Modelo de Dados Simplificado

```mermaid
erDiagram
    CLIENTE ||--o{ VALE : possui
    VALE ||--|{ ITEM_VALE : contem
    VALE ||--o{ RESERVA_ESTOQUE : gera
    VALE ||--o{ PEDIDO_VENDA : converte_em
    VALE ||--o{ ORDEM_SERVICO : converte_em
    VALE ||--o{ NOTA_FISCAL_ORQUESTRADA : origina
    VALE ||--o{ BOLETO_ORQUESTRADO : origina
    VALE ||--o{ ORDEM_SERVICO_GARANTIA : referencia

    BATERIA ||--o{ ITEM_VALE : vendida_em
    BATERIA ||--o{ RESERVA_ESTOQUE : reservada_em
    BATERIA ||--o{ ORDEM_SERVICO_GARANTIA : analisada_em

    ROTA_ENTREGA ||--|{ PONTO_ENTREGA : possui
    PONTO_ENTREGA }o--|| VALE : entrega_relacionada
    PONTO_ENTREGA ||--o{ RECEBIMENTO_MOVEL : recebe

    CLIENTE ||--o{ CONTA_SUCATA_MOVIMENTACAO : acumula
    CLIENTE ||--o{ ORDEM_SERVICO_GARANTIA : abre

    VALE {
      bigint id
      bigint cliente_id
      string status
      decimal valor_total
    }
    ITEM_VALE {
      bigint id
      bigint vale_id
      bigint bateria_id
      int quantidade
      decimal preco_unitario
    }
    RESERVA_ESTOQUE {
      bigint id
      bigint vale_id
      bigint item_vale_id
      string status
    }
    PEDIDO_VENDA {
      bigint id
      bigint vale_id
      string status
      decimal valor_total
    }
    NOTA_FISCAL_ORQUESTRADA {
      bigint id
      bigint vale_id
      string status
      string idempotency_key
    }
    BOLETO_ORQUESTRADO {
      bigint id
      bigint vale_id
      string status
      string idempotency_key
    }
    ORDEM_SERVICO_GARANTIA {
      bigint id
      bigint cliente_id
      bigint vale_original_id
      bigint bateria_id
      string status
      string resultado
    }
    ROTA_ENTREGA {
      bigint id
      bigint entregador_id
      string status
      datetime data_saida
    }
    PONTO_ENTREGA {
      bigint id
      bigint rota_entrega_id
      bigint vale_id
      string status
    }
    RECEBIMENTO_MOVEL {
      bigint id
      bigint ponto_entrega_id
      decimal valor
      string metodo_pagamento
    }
    CONTA_SUCATA_MOVIMENTACAO {
      bigint id
      bigint cliente_id
      decimal peso_kg
      decimal credito_valor
    }
    CLIENTE {
      bigint id
      string razao_social
      string status
    }
    BATERIA {
      bigint id
      string sku
      string marca
      string tecnologia
    }
    ORDEM_SERVICO {
      bigint id
      bigint vale_id
      string status
    }
```
