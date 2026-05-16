# Data Model: Módulo 012 - Platform Payments and Reconciliation

## 1. GatewayCobrancaSaaS

**Purpose**: representar o provedor externo configurado para emissão e acompanhamento de cobranças SaaS.

**Core fields**:
- `id`
- `nome`
- `slug`
- `driver`
- `status` (`active`, `inactive`, `degraded`)
- `supported_channels`
- `credential_profile`
- `timeout_seconds`
- `metadata`
- `created_at`
- `updated_at`

**Validation rules**:
- `slug` único
- um gateway não pode ser marcado como `active` sem perfil mínimo de credenciais

## 2. CobrancaSaaSExterna

**Purpose**: representar a materialização externa de uma `FaturaSaaS` em um gateway de pagamento.

**Core fields**:
- `id`
- `fatura_saas_id`
- `gateway_cobranca_saas_id`
- `external_charge_id`
- `external_reference`
- `payment_channel` (`boleto`, `pix`, `card`, `manual`, `other`)
- `status` (`draft`, `submitted`, `pending`, `paid`, `expired`, `cancelled`, `failed`, `refunded`, `chargeback`)
- `valor_emitido`
- `vencimento_emitido`
- `issued_at`
- `paid_at`
- `cancelled_at`
- `failure_reason`
- `idempotency_key`
- `metadata`
- `created_at`
- `updated_at`

**Relationships**:
- pertence a uma `FaturaSaaS`
- pertence a um `GatewayCobrancaSaaS`
- possui muitos `RetornoPagamentoSaaS`
- possui uma ou muitas `ConciliacaoPagamentoSaaS`

**Validation rules**:
- uma cobrança externa ativa por `fatura_saas_id` e contexto operacional, salvo reemissão rastreável
- `external_charge_id` único por gateway quando informado

## 3. RetornoPagamentoSaaS

**Purpose**: representar webhook, callback ou carga assíncrona enviada pelo provedor.

**Core fields**:
- `id`
- `gateway_cobranca_saas_id`
- `cobranca_saas_externa_id`
- `source_type` (`webhook`, `polling`, `import`, `manual_replay`)
- `external_event_id`
- `external_reference`
- `event_type`
- `payload`
- `received_at`
- `processed_at`
- `processing_status` (`pending`, `processed`, `ignored`, `failed`)
- `processing_error`
- `idempotency_key`
- `metadata`
- `created_at`
- `updated_at`

**Validation rules**:
- retorno deve ser idempotente por `gateway + external_event_id` ou chave equivalente
- payload bruto precisa permanecer preservado para auditoria e replay

## 4. ConciliacaoPagamentoSaaS

**Purpose**: representar o resultado da comparação entre retorno externo e obrigação financeira central.

**Core fields**:
- `id`
- `fatura_saas_id`
- `cobranca_saas_externa_id`
- `retorno_pagamento_saas_id`
- `status` (`matched`, `partially_matched`, `exception`, `replayed`, `reversed`)
- `reconciliation_type` (`automatic`, `manual`, `replay`)
- `expected_amount`
- `received_amount`
- `difference_amount`
- `reconciled_at`
- `operator_user_id`
- `notes`
- `metadata`
- `created_at`
- `updated_at`

**Validation rules**:
- conciliação automática só pode ser `matched` quando referência, valor e estado forem compatíveis
- diferenças positivas ou negativas devem ser registradas explicitamente

## 5. ExcecaoConciliacaoSaaS

**Purpose**: representar casos que exigem análise humana antes de alterar definitivamente o estado financeiro ou comercial.

**Core fields**:
- `id`
- `fatura_saas_id`
- `cobranca_saas_externa_id`
- `retorno_pagamento_saas_id`
- `conciliacao_pagamento_saas_id`
- `status` (`open`, `investigating`, `resolved`, `dismissed`)
- `exception_type` (`reference_mismatch`, `amount_mismatch`, `duplicate_event`, `chargeback`, `refund`, `gateway_failure`, `unknown`)
- `severity` (`low`, `medium`, `high`, `critical`)
- `impact_on_subscription` (`none`, `hold`, `review_block`, `reactivation_review`)
- `opened_at`
- `resolved_at`
- `owner_user_id`
- `resolution_notes`
- `metadata`
- `created_at`
- `updated_at`

## State Transitions

### CobrancaSaaSExterna
- `draft` → `submitted`
- `submitted` → `pending`
- `pending` → `paid`
- `pending` → `expired`
- `pending` → `cancelled`
- `pending` → `failed`
- `paid` → `refunded`
- `paid` → `chargeback`

### RetornoPagamentoSaaS
- `pending` → `processed`
- `pending` → `ignored`
- `pending` → `failed`

### ConciliacaoPagamentoSaaS
- `automatic` → `matched`
- `automatic` → `exception`
- `exception` → `replayed`
- `exception` → `matched`
- `matched` → `reversed`

### ExcecaoConciliacaoSaaS
- `open` → `investigating`
- `investigating` → `resolved`
- `investigating` → `dismissed`
- `resolved` → `open` em caso de reabertura auditável

## Notes

- Todas as entidades deste módulo vivem no banco central e não no banco do tenant.
- A `FaturaSaaS` do módulo `011` continua sendo a obrigação financeira central; o `012` adiciona sua materialização externa e sua reconciliação.
- Eventos críticos de emissão, liquidação, divergência e reversão devem gerar publicação compatível com o backbone `010`.
