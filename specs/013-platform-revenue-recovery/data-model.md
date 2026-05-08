# Data Model: Módulo 013 - Platform Revenue Recovery

## 1. PoliticaRecuperacaoReceita

**Purpose**: representar a política central que define estágios da régua, canais, janelas e critérios de escalonamento.

**Core fields**:
- `id`
- `nome`
- `slug`
- `status` (`draft`, `active`, `inactive`)
- `entry_conditions`
- `stage_definitions`
- `escalation_rules`
- `reengagement_rules`
- `metadata`
- `created_at`
- `updated_at`

**Validation rules**:
- `slug` único
- apenas uma política `active` por carteira operacional quando a governança exigir exclusividade

## 2. CasoRecuperacaoReceita

**Purpose**: representar o acompanhamento operacional de uma obrigação financeira ou assinante dentro da régua.

**Core fields**:
- `id`
- `cliente_id`
- `assinatura_id`
- `fatura_saas_id`
- `politica_recuperacao_receita_id`
- `status` (`open`, `paused`, `escalated`, `recovered`, `closed`, `cancelled`)
- `entry_reason` (`invoice_overdue`, `payment_failed`, `reopened_chargeback`, `manual`)
- `current_stage`
- `severity`
- `opened_at`
- `closed_at`
- `owner_user_id`
- `last_action_at`
- `metadata`
- `created_at`
- `updated_at`

**Relationships**:
- pertence a `Cliente`
- pertence a `AssinaturaPlataforma`
- pertence a `FaturaSaaS`
- pertence a `PoliticaRecuperacaoReceita`
- possui muitas `AcaoRecuperacaoReceita`
- possui muitos `CompromissoPagamento`

**Validation rules**:
- um caso ativo por obrigação financeira e política, salvo reabertura auditável

## 3. AcaoRecuperacaoReceita

**Purpose**: representar a ação planejada ou executada dentro da régua de recuperação.

**Core fields**:
- `id`
- `caso_recuperacao_receita_id`
- `action_type` (`automated_reminder`, `manual_follow_up`, `escalation`, `promise_follow_up`, `reengagement`, `replay`)
- `channel` (`email`, `whatsapp`, `phone`, `internal_task`, `other`)
- `stage_name`
- `status` (`scheduled`, `processing`, `sent`, `completed`, `failed`, `cancelled`, `skipped`)
- `idempotency_key`
- `scheduled_for`
- `executed_at`
- `result_code`
- `operator_user_id`
- `payload_snapshot`
- `metadata`
- `created_at`
- `updated_at`

**Validation rules**:
- deduplicação por `caso + stage_name + channel + idempotency_key`
- uma ação cancelada ou executada não pode ser sobrescrita silenciosamente

## 4. CompromissoPagamento

**Purpose**: representar promessa ou acordo manual registrado durante a operação de cobrança.

**Core fields**:
- `id`
- `caso_recuperacao_receita_id`
- `promised_amount`
- `promised_date`
- `status` (`open`, `honored`, `broken`, `cancelled`)
- `recorded_by_user_id`
- `notes`
- `suspends_until`
- `metadata`
- `created_at`
- `updated_at`

**Validation rules**:
- um compromisso `open` vigente deve bloquear apenas ações incompatíveis até `suspends_until`

## 5. IndicadorRecuperacaoReceita

**Purpose**: representar snapshots agregados para painel central de recuperação.

**Core fields**:
- `id`
- `reference_date`
- `channel`
- `stage_name`
- `open_cases`
- `escalated_cases`
- `recovered_cases`
- `broken_promises`
- `recovery_amount`
- `metadata`
- `created_at`
- `updated_at`

## State Transitions

### PoliticaRecuperacaoReceita
- `draft` → `active`
- `active` → `inactive`

### CasoRecuperacaoReceita
- `open` → `paused`
- `open` → `escalated`
- `open` → `recovered`
- `paused` → `open`
- `escalated` → `recovered`
- `escalated` → `closed`
- `recovered` → `closed`
- `open` → `cancelled`

### AcaoRecuperacaoReceita
- `scheduled` → `processing`
- `processing` → `sent`
- `processing` → `completed`
- `processing` → `failed`
- `scheduled` → `cancelled`
- `scheduled` → `skipped`
- `failed` → `replay`

### CompromissoPagamento
- `open` → `honored`
- `open` → `broken`
- `open` → `cancelled`
- `broken` → `open` em caso de renegociação auditável

## Notes

- Todas as entidades do módulo `013` vivem no banco central.
- A `FaturaSaaS` do `011` continua sendo a obrigação financeira; o `012` continua sendo a origem dos eventos de pagamento; o `013` adiciona a orquestração de recuperação.
- Eventos críticos de abertura de caso, escalonamento, promessa, recuperação e reengajamento devem ser compatíveis com o backbone `010`.
