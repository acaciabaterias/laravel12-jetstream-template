# Data Model: Módulo 020 - Advanced Revenue Recovery Automation

## RecoveryAutomationPolicyVersion

**Purpose**: representar uma versão publicável da estratégia automatizada de recuperação.

**Fields**:

- `id`
- `slug`
- `name`
- `description`
- `status` (`draft`, `active`, `superseded`, `rolled_back`)
- `scope_filters` (segmento, carteira, severidade, atraso, holdout)
- `guardrail_rules` (frequência máxima, supressão, cooldown, fallback obrigatório)
- `fallback_matrix`
- `activation_started_at`
- `activation_completed_at`
- `superseded_by_policy_version_id`
- `created_by`
- `approved_by`
- `rolled_back_by`
- `metadata`

**Relationships**:

- has many `RecoveryAutomationJourney`
- has many `RecoveryAutomationExperiment`
- has many `RecoveryAutomationViolation`

## RecoveryAutomationJourney

**Purpose**: representar a jornada automatizada atribuída a um `CasoRecuperacaoReceita`.

**Fields**:

- `id`
- `caso_recuperacao_receita_id`
- `recovery_automation_policy_version_id`
- `recovery_automation_experiment_id` nullable
- `variant_key`
- `journey_status` (`pending`, `active`, `paused`, `completed`, `rolled_back`)
- `current_stage`
- `current_channel`
- `last_dispatched_at`
- `next_evaluation_at`
- `suppressed_until`
- `rollback_marked_at`
- `metadata`

**Relationships**:

- belongs to `CasoRecuperacaoReceita`
- belongs to `RecoveryAutomationPolicyVersion`
- belongs to `RecoveryAutomationExperiment`
- has many `RecoveryAutomationDispatch`

## RecoveryAutomationDispatch

**Purpose**: representar um disparo automático planejado ou executado pela jornada.

**Fields**:

- `id`
- `recovery_automation_journey_id`
- `acao_recuperacao_receita_id` nullable
- `dispatch_key`
- `stage_key`
- `channel`
- `template_key`
- `attempt_number`
- `dispatch_status` (`scheduled`, `dispatched`, `failed`, `suppressed`, `cancelled`, `replayed`)
- `fallback_reason`
- `scheduled_for`
- `dispatched_at`
- `result_payload`
- `operator_id` nullable
- `metadata`

**Relationships**:

- belongs to `RecoveryAutomationJourney`
- belongs to `AcaoRecuperacaoReceita`

## RecoveryAutomationExperiment

**Purpose**: representar um experimento controlado ou grupo de holdout aplicado a jornadas automatizadas.

**Fields**:

- `id`
- `recovery_automation_policy_version_id`
- `name`
- `status` (`draft`, `running`, `completed`, `cancelled`)
- `allocation_rules`
- `control_ratio`
- `variant_definitions`
- `started_at`
- `ended_at`
- `created_by`
- `metadata`

**Relationships**:

- belongs to `RecoveryAutomationPolicyVersion`
- has many `RecoveryAutomationJourney`

## RecoveryAutomationViolation

**Purpose**: representar uma violação material ou degradação operacional detectada na automação.

**Fields**:

- `id`
- `recovery_automation_policy_version_id`
- `recovery_automation_journey_id` nullable
- `recovery_automation_dispatch_id` nullable
- `violation_type` (`frequency_limit`, `out_of_window`, `fallback_exhausted`, `performance_regression`, `duplicate_dispatch`)
- `severity` (`low`, `medium`, `high`, `critical`)
- `detected_at`
- `resolved_at` nullable
- `resolution_status` (`open`, `acknowledged`, `resolved`, `rolled_back`)
- `summary`
- `evidence_payload`
- `resolved_by` nullable

**Relationships**:

- belongs to `RecoveryAutomationPolicyVersion`
- belongs to `RecoveryAutomationJourney`
- belongs to `RecoveryAutomationDispatch`

## Validation Rules

- Apenas uma `RecoveryAutomationPolicyVersion` pode ficar `active` para o mesmo escopo material.
- Uma `RecoveryAutomationJourney` não pode trocar de `variant_key` depois de ativada.
- `dispatch_key` deve ser único por jornada, estágio, canal e janela operacional.
- Um `RecoveryAutomationDispatch` só pode virar `replayed` se existir dispatch original falho ou cancelado com replay autorizado.
- `RecoveryAutomationViolation` crítica deve bloquear nova publicação da mesma política até resolução ou rollback.

## State Notes

- `RecoveryAutomationPolicyVersion` percorre `draft -> active -> superseded` ou `draft/active -> rolled_back`.
- `RecoveryAutomationJourney` percorre `pending -> active -> paused/completed` e pode ser marcada como `rolled_back`.
- `RecoveryAutomationExperiment` percorre `draft -> running -> completed/cancelled`.
- Dispatches e violações devem preservar vínculo com `CasoRecuperacaoReceita` e `AcaoRecuperacaoReceita` do módulo `013`.
