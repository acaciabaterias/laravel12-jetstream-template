# Quickstart: Módulo 013 - Platform Revenue Recovery

## Objetivo

Validar localmente a base de recuperação de receita do SaaS após falha de pagamento, atraso ou divergência persistente, antes de automatizar campanhas e escalonamentos em produção.

## Pré-requisitos

- banco central configurado
- módulo `011` operacional com `AssinaturaPlataforma` e `FaturaSaaS`
- módulo `012` operacional com sinais de falha, atraso, liquidação e chargeback
- backbone `010` operacional para eventos e replay
- autenticação de super admin e perfil de billing operacional
- suíte de testes atual passando como baseline

## Sequência sugerida

1. Criar migrations centrais para políticas, casos, ações, compromissos e indicadores de recuperação.
2. Implementar serviços base de avaliação da régua, deduplicação de ações e escalonamento.
3. Adicionar fluxos administrativos para promessa de pagamento, acompanhamento humano e inspeção de backlog.
4. Implementar encerramento automático do caso após regularização financeira.
5. Integrar publicação dos eventos de recuperação no backbone `010`.
6. Validar replay de ações falhas e reabertura controlada por chargeback ou promessa quebrada.

## Cenários de validação

- Abrir caso de recuperação para `FaturaSaaS` vencida e confirmar criação do estágio inicial.
- Detectar falha de cobrança do `012` e confirmar abertura da régua sem duplicar caso existente.
- Registrar promessa de pagamento e confirmar suspensão seletiva das ações incompatíveis.
- Simular atraso crítico ou reincidência e confirmar escalonamento humano com responsável.
- Confirmar encerramento automático do caso após liquidação válida da fatura.
- Reabrir caso por chargeback e confirmar preservação do histórico anterior.
- Consultar painel central filtrando casos por estágio, canal, severidade e responsável.

## Critérios para avançar à implementação completa

- política de entrada e progressão da régua documentada
- deduplicação por estágio/canal definida
- promessas de pagamento e escalonamentos com trilha auditável
- encerramento automático por regularização financeira comprovado
- eventos mínimos de recuperação definidos para o backbone `010`
- rollback operacional documentado para casos, ações e compromissos centrais

## Evidência de validação executada

- `vendor/bin/pint --dirty --format=agent`
- `php artisan test --compact tests/Feature/PlatformRevenueRecoveryFoundationTest.php tests/Feature/PlatformRevenueRecoveryOpenCaseTest.php tests/Feature/PlatformRevenueRecoveryDeduplicationTest.php tests/Feature/PlatformRevenueRecoveryEscalationTest.php tests/Feature/PlatformRevenueRecoveryPromiseTest.php tests/Feature/PlatformRevenueRecoveryDashboardTest.php tests/Feature/PlatformRevenueRecoveryFiltersTest.php tests/Unit/PlatformRevenueRecoveryIdempotencyTest.php tests/Unit/PlatformRevenueRecoveryRulesTest.php tests/Unit/PlatformRevenueRecoverySummaryTest.php`
- suíte completa validada no ciclo: `347 passed`, `1 skipped`, `1635 assertions`
