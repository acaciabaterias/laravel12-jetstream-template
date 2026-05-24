# Quickstart: Módulo 023 - Fiscal CFOP Export/Import

## Objetivo

Validar o fluxo central de governança fiscal cobrindo consulta por cenário, publicação governada de catálogo/CFOP, resolução material para contexto interestadual, inspeção de inconsistências tributárias e rollback da última publicação saudável.

## Pré-requisitos

- banco central configurado
- autenticação administrativa do módulo `002` operacional
- backbone `010` disponível para eventos materiais
- módulo `009` operacional como consumidor do contrato fiscal enriquecido
- publicação fiscal ativa com `tax_profile` completo por cenário material

## Sequência sugerida

1. Autenticar um operador fiscal no painel administrativo.
2. Publicar uma release fiscal com cenários obrigatórios de exportação/importação e seus CFOPs correspondentes.
3. Confirmar que cada `scenarioMapping` publicado inclui `tax_profile` com `ncm_code`, `tax_regime`, `CST` ou `CSOSN`, `partner_type`, `operation_purpose` e `tax_payload`.
4. Consultar o cenário `direct_export` no painel central e confirmar retorno de CFOP, direção fiscal, flags de validação e `tax_profile`.
5. Consultar um cenário material interestadual como `interstate_resale`, informando `origin_state`, `destination_state`, `partner_type`, `operation_purpose` e `tax_regime`.
6. Confirmar que a resolução interestadual expõe `tax_context.is_interstate`, `interstate_tax_rate` e a publicação de origem.
7. Validar o contrato consumível do módulo `009` em `GET /admin/fiscal-rules/resolve`.
8. Consultar a inspeção central de regras fiscais e confirmar contagem de `material_tax_issues` e perfis interestaduais ativos.
9. Registrar uma publicação degradada, por exemplo sem `NCM`, sem `CST/CSOSN` ou sem alíquota interestadual aplicável.
10. Executar rollback para a publicação anterior saudável.
11. Confirmar eventos materiais e metadados de rollback no backbone `010`.

## Cenários de validação

- Cenário sem regra ativa deve cair no fallback governado com issue report aberto.
- Publicação com CFOP inválido, cenário obrigatório ausente ou `tax_profile` incompleto deve ser bloqueada ou marcada como inconsistente.
- Regra interestadual com origem e destino diferentes deve exigir alíquota interestadual e contexto tributário compatível.
- Contrato consumível em `/admin/fiscal-rules/resolve` deve devolver `schema_version`, `resolution`, `tax_profile`, `tax_context` e referência da publicação de origem.
- Inconsistência material deve gerar relatório inspecionável.
- Rollback deve restaurar a última publicação saudável sem apagar histórico.

## Critérios para avançar ao fechamento

- consulta fiscal resolvida por cenário no plano central
- publicação governada com snapshot do catálogo, cenários e `tax_profile` material
- contrato reutilizável para o módulo `009` retornando payload fiscal suficiente para emissão assistida
- inspeção JSON retornando publicações, mappings, inconsistências e `material_tax_issues`
- rollback auditável restaurando a última publicação saudável com evidência tributária
- runbook operacional cobrindo publicação, fallback, resolução interestadual e reversão fiscal

## Evidência de validação

- suíte focal do módulo:
  - `php artisan test --compact tests/Feature/PlatformFiscalRuleFoundationTest.php tests/Feature/PlatformFiscalScenarioLookupTest.php tests/Feature/PlatformFiscalFallbackTest.php tests/Feature/PlatformFiscalPublicationTest.php tests/Feature/PlatformFiscalCoverageTest.php tests/Feature/PlatformFiscalInspectionTest.php tests/Feature/PlatformFiscalRollbackTest.php tests/Feature/PlatformFiscalGovernanceTest.php tests/Feature/PlatformFiscalTaxProfilePublicationTest.php tests/Feature/PlatformFiscalInterstateLookupTest.php tests/Unit/PlatformFiscalResolutionRulesTest.php tests/Unit/PlatformFiscalPublicationRulesTest.php tests/Unit/PlatformFiscalRollbackRulesTest.php tests/Unit/PlatformFiscalTaxProfileRulesTest.php`
- resultado focal atual:
  - `21` testes passando
  - `113` assertions
