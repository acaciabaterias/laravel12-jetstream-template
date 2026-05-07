# Data Model: Módulo 011 - Platform Billing Control Plane

## 1. PlanoComercial

**Purpose**: representar a oferta comercial da plataforma.

**Core fields**:
- `id`
- `codigo`
- `nome`
- `descricao`
- `periodicidade` (`mensal`, `trimestral`, `anual`, `custom`)
- `valor_base`
- `status` (`draft`, `active`, `inactive`, `deprecated`)
- `limites_operacionais`
- `beneficios`
- `created_at`
- `updated_at`

**Validation rules**:
- `codigo` único
- `valor_base` obrigatório e não negativo
- plano não pode ser ativado sem periodicidade e valor definidos

## 2. AssinaturaPlataforma

**Purpose**: representar o contrato entre a plataforma e o assinante.

**Core fields**:
- `id`
- `cliente_id`
- `plano_comercial_id`
- `status` (`draft`, `trial`, `active`, `grace_period`, `blocked`, `cancelled`, `expired`)
- `vigencia_inicio`
- `vigencia_fim`
- `proxima_cobranca_em`
- `grace_ends_at`
- `cancelled_at`
- `cancel_reason`
- `created_at`
- `updated_at`

**Relationships**:
- pertence a um `Cliente`
- pertence a um `PlanoComercial`
- possui muitas `FaturaSaaS`
- possui muitos `EventoComercialAssinante`

## 3. FaturaSaaS

**Purpose**: representar a cobrança da assinatura em um período comercial.

**Core fields**:
- `id`
- `assinatura_plataforma_id`
- `competencia`
- `valor_total`
- `vencimento`
- `status` (`draft`, `pending`, `paid`, `overdue`, `cancelled`, `written_off`)
- `paid_at`
- `external_reference`
- `billing_channel`
- `metadata`
- `created_at`
- `updated_at`

**Validation rules**:
- uma fatura por assinatura + competência + referência de cobrança
- `valor_total` obrigatório e não negativo

## 4. PoliticaInadimplencia

**Purpose**: representar as regras configuráveis para cobrança, tolerância e bloqueio.

**Core fields**:
- `id`
- `nome`
- `grace_period_days`
- `block_after_days`
- `reactivation_mode`
- `notification_profile`
- `status` (`active`, `inactive`)
- `created_at`
- `updated_at`

## 5. EventoComercialAssinante

**Purpose**: representar a trilha operacional de mudanças de estado comercial.

**Core fields**:
- `id`
- `cliente_id`
- `assinatura_plataforma_id`
- `event_type` (`plan_changed`, `invoice_overdue`, `grace_started`, `blocked`, `unblocked`, `cancelled`, `reactivated`)
- `before_state`
- `after_state`
- `effective_at`
- `actor_user_id`
- `reason`
- `metadata`
- `created_at`

## State Transitions

### AssinaturaPlataforma
- `draft` → `trial`
- `draft` → `active`
- `trial` → `active`
- `active` → `grace_period`
- `grace_period` → `blocked`
- `blocked` → `active`
- `active` → `cancelled`
- `grace_period` → `cancelled`
- `blocked` → `cancelled`

### FaturaSaaS
- `draft` → `pending`
- `pending` → `paid`
- `pending` → `overdue`
- `overdue` → `paid`
- `pending` → `cancelled`
- `overdue` → `written_off`

## Notes

- Todas as entidades deste módulo vivem no banco central e não no banco do tenant.
- O vínculo operacional com o tenant é sempre indireto, através do assinante central (`cliente_id`).
- Mudanças críticas de estado devem gerar `EventoComercialAssinante` e publicação compatível com o backbone `010`.
