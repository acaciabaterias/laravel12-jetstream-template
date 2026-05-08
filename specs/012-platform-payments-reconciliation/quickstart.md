# Quickstart: Módulo 012 - Platform Payments and Reconciliation

## Objetivo

Validar localmente a base de emissão, retorno e reconciliação de pagamentos SaaS antes de automatizar cobrança recorrente em produção.

## Pré-requisitos

- banco central configurado
- módulo `011` operacional com `FaturaSaaS` funcional
- backbone `010` operacional para eventos e replay
- autenticação de super admin operacional
- suíte de testes atual passando como baseline

## Sequência sugerida

1. Criar migrations centrais para gateways, cobranças externas, retornos, conciliações e exceções.
2. Implementar modelos e serviços base de emissão, webhook e reconciliação.
3. Adicionar fluxos administrativos para inspeção de cobranças e divergências.
4. Implementar baixa automática segura e fila de exceções operacionais.
5. Integrar publicação dos eventos financeiros centrais no backbone `010`.
6. Validar replay controlado e impacto comercial sobre o módulo `011`.

## Cenários de validação

- Emitir uma `FaturaSaaS` elegível em gateway configurado e confirmar vínculo externo.
- Receber um retorno de pagamento válido e confirmar baixa automática com auditoria.
- Reprocessar um webhook duplicado e confirmar idempotência.
- Simular divergência de valor e confirmar abertura de exceção operacional.
- Simular chargeback ou estorno e confirmar preservação do histórico financeiro original.
- Consultar visão central filtrando cobranças pendentes, liquidadas e divergentes.
- Executar replay manual de retorno e confirmar auditoria explícita do reprocessamento.

## Critérios para avançar à implementação completa

- emissão externa mínima validada para ao menos um gateway suportado
- política de idempotência para webhook e replay documentada
- baixa automática segura sem duplicidade operacional
- divergências e reversões visíveis em trilha auditável
- eventos financeiros mínimos definidos para o backbone `010`
- rollback operacional documentado para mudança de estado financeiro crítica

## Evidência de validação executada

- `vendor/bin/pint --dirty --format=agent`
- `php artisan test --compact tests/Feature/PlatformPaymentsFoundationTest.php tests/Feature/PlatformPaymentsChargeIssuanceTest.php tests/Feature/PlatformPaymentsDuplicateIssuanceTest.php tests/Feature/PlatformPaymentsWebhookSettlementTest.php tests/Feature/PlatformPaymentsWebhookIdempotencyTest.php tests/Feature/PlatformPaymentsReplayFlowTest.php tests/Feature/PlatformPaymentsDashboardTest.php tests/Feature/PlatformPaymentsExceptionFiltersTest.php tests/Unit/PlatformPaymentsIdempotencyTest.php tests/Unit/PlatformPaymentsReconciliationRuleTest.php tests/Unit/PlatformPaymentsExceptionClassifierTest.php`
- `php artisan test --compact tests/Feature/ReadmeBadgesTest.php tests/Feature/PlatformPaymentsReplayFlowTest.php tests/Unit/PlatformPaymentsPublicationTest.php`
- suíte completa validada no ciclo: `331 passed`, `1 skipped`, `1576 assertions`
