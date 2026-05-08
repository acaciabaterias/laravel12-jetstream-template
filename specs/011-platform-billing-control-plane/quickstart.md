# Quickstart: Módulo 011 - Platform Billing Control Plane

## Objetivo

Validar localmente a base do plano de controle comercial da plataforma antes de automatizar cobrança e bloqueio em produção.

## Pré-requisitos

- banco central configurado
- migrations centrais funcionais
- autenticação de super admin operacional
- suíte de testes atual passando como baseline

## Sequência sugerida

1. Criar migrations centrais para `planos_comerciais`, `assinaturas_plataforma`, `faturas_saas`, `politicas_inadimplencia` e `eventos_comerciais_assinante`.
2. Implementar modelos e serviços base de governança comercial.
3. Adicionar painel administrativo para gestão de plano, assinatura e carteira vencida.
4. Implementar avaliação de grace period, bloqueio e reativação.
5. Integrar publicação dos eventos comerciais mínimos no backbone `010`.
6. Validar filtros operacionais e trilha de auditoria.

## Cenários de validação

- Criar um plano e ativar assinatura para um assinante existente.
- Gerar uma fatura SaaS e simular vencimento com entrada em grace period.
- Aplicar bloqueio comercial elegível e confirmar trilha operacional.
- Regularizar a cobrança e confirmar desbloqueio com evento auditável.
- Consultar o painel central filtrando bloqueados, grace period e reativações recentes.

## Critérios para avançar à implementação completa

- catálogo inicial de planos validado
- política de inadimplência mínima documentada
- bloqueio e desbloqueio rastreáveis sem ambiguidade de estado
- eventos comerciais mínimos definidos para o backbone `010`
- rollback operacional documentado para mudança de estado comercial crítica

## Evidência de validação executada

- `vendor/bin/pint --dirty --format=agent`
- `php artisan test --compact tests/Feature/PlatformBillingSubscriptionLifecycleTest.php tests/Feature/PlatformBillingPlanChangeTest.php tests/Feature/PlatformBillingDelinquencyPolicyTest.php tests/Feature/PlatformBillingBlockReactivationTest.php tests/Feature/PlatformBillingDashboardTest.php tests/Feature/PlatformBillingPortfolioFiltersTest.php tests/Feature/PlatformBillingPlanCatalogManagerTest.php tests/Feature/PlatformBillingSubscriptionManagerTest.php tests/Feature/PlatformBillingInspectionTest.php tests/Feature/PlatformBillingBackbonePublicationTest.php tests/Unit/DelinquencyPolicyEvaluatorTest.php tests/Unit/PlatformBillingSummaryAggregatorTest.php tests/Unit/PlatformSubscriptionStateMachineTest.php`
- suíte completa validada no ciclo: `310 passed`, `1 skipped`, `1507 assertions`
