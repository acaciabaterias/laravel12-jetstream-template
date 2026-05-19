# Quickstart: Módulo 023 - Fiscal CFOP Export/Import

## Objetivo

Validar o fluxo central de governança fiscal cobrindo consulta por cenário, publicação governada de catálogo/CFOP, inspeção de inconsistências e rollback da última publicação saudável.

## Pré-requisitos

- banco central configurado
- autenticação administrativa do módulo `002` operacional
- backbone `010` disponível para eventos materiais
- módulo `009` operacional como consumidor potencial do catálogo fiscal

## Sequência sugerida

1. Autenticar um operador fiscal no painel administrativo.
2. Publicar uma release fiscal com cenários obrigatórios de exportação/importação e seus CFOPs correspondentes.
3. Consultar o cenário `direct_export` no painel central.
4. Confirmar o retorno de CFOP, direção fiscal e flags de validação.
5. Consultar a inspeção central de regras fiscais.
6. Registrar uma publicação degradada ou com cenário obrigatório ausente.
7. Executar rollback para a publicação anterior saudável.
8. Confirmar os eventos materiais no backbone `010`.

## Cenários de validação

- Cenário sem regra ativa deve cair no fallback governado com issue report aberto.
- Publicação com CFOP inválido ou cenário obrigatório ausente deve ser bloqueada ou marcada como inconsistente.
- Inconsistência material deve gerar relatório inspecionável.
- Rollback deve restaurar a última publicação saudável sem apagar histórico.

## Critérios para avançar ao fechamento

- consulta fiscal resolvida por cenário no plano central
- publicação governada com snapshot do catálogo e dos cenários
- inspeção JSON retornando publicações, mappings e inconsistências
- rollback auditável restaurando a última publicação saudável
- runbook operacional cobrindo publicação, fallback e reversão fiscal

## Evidência de validação

- suíte focal do módulo:
  - `php artisan test --compact tests/Feature/PlatformFiscalRuleFoundationTest.php tests/Feature/PlatformFiscalScenarioLookupTest.php tests/Feature/PlatformFiscalFallbackTest.php tests/Feature/PlatformFiscalPublicationTest.php tests/Feature/PlatformFiscalCoverageTest.php tests/Feature/PlatformFiscalInspectionTest.php tests/Feature/PlatformFiscalRollbackTest.php tests/Feature/PlatformFiscalGovernanceTest.php tests/Unit/PlatformFiscalResolutionRulesTest.php tests/Unit/PlatformFiscalPublicationRulesTest.php tests/Unit/PlatformFiscalRollbackRulesTest.php`
- resultado focal esperado no fechamento:
  - `17` testes passando
  - `83` assertions
