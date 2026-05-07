# Quickstart: Módulo 010 - Backbone de Integração e Observabilidade

## Objetivo

Validar localmente a base do backbone de integração antes de conectar todos os módulos produtores e consumidores.

## Pré-requisitos

- dependências PHP instaladas
- banco de testes funcional
- Redis disponível para cenários de fila e retry
- suíte de testes atual passando como baseline

## Sequência sugerida

1. Criar as migrations tenant-aware de `outbox`, `inbox`, `entregas_integracao`, `contratos_evento` e `endpoints_integracao`.
2. Implementar modelos e serviços base de publicação e consumo.
3. Adicionar dispatcher assíncrono e política de retry.
4. Registrar o primeiro contrato canônico de evento usando um fluxo já existente, como `VALE_FATURADO`.
5. Implementar testes mínimos:
   - persistência em outbox
   - publicação com retry
   - consumo idempotente
   - dead-letter e replay
6. Expor métricas iniciais e painel operacional de inspeção.

## Cenários de validação

- Publicar um evento de venda concluída e confirmar rastreabilidade completa.
- Simular indisponibilidade do destino e confirmar retry sem perda do evento.
- Reenviar o mesmo evento e confirmar bloqueio de duplicidade funcional.
- Mover um evento para dead-letter e executar replay controlado.

## Critérios para avançar ao rollout

- contratos mínimos catalogados para pelo menos um evento fiscal e um bancário
- replay manual validado em ambiente controlado
- métricas básicas visíveis por tenant e por serviço
- rollback operacional documentado para estados de outbox/inbox

## Evidência de validação atual

Validação automatizada confirmada neste ciclo:

```bash
php artisan test --compact tests/Feature/IntegrationBackboneFoundationTest.php \
  tests/Feature/IntegrationBackbonePublicationTest.php \
  tests/Feature/IntegrationBackboneRetryTest.php \
  tests/Feature/IntegrationBackboneInboxTest.php \
  tests/Feature/IntegrationBackboneReplayFlowTest.php \
  tests/Feature/IntegrationBackboneDashboardTest.php \
  tests/Feature/IntegrationGatewayInspectionTest.php \
  tests/Unit/IntegrationEventEnvelopeTest.php \
  tests/Unit/IntegrationReplayPolicyTest.php \
  tests/Unit/IntegrationContractCatalogTest.php
```

Resultado observado:

- `24 passed`
- `95 assertions`

Validação de regressão global após integração do módulo:

```bash
php artisan test --compact
```

Resultado observado:

- `288 passed`
- `1 skipped`
- `1422 assertions`
