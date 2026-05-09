# Quickstart: Módulo 014 - Platform Commercial Analytics

## Objetivo

Validar localmente a base de analytics comercial da plataforma após a estabilização de billing, payments e recovery, antes de automatizar dashboards executivos em produção.

## Pré-requisitos

- banco central configurado
- módulo `011` operacional com assinaturas e faturas SaaS
- módulo `012` operacional com liquidação, falha e reconciliação
- módulo `013` operacional com casos de recovery, promessas e escalonamentos
- backbone `010` operacional para eventos e rastreabilidade
- suíte de testes atual passando como baseline

## Sequência sugerida

1. Criar migrations centrais para snapshots, coortes, performance por canal, insights de risco e drill-down.
2. Implementar serviços base de agregação, segmentação e rebuild.
3. Adicionar painel executivo com filtros por período, coorte e canal.
4. Implementar endpoint de inspeção para drill-down operacional.
5. Integrar publicação de eventos analíticos materiais no backbone `010`.
6. Validar rebuild de snapshot após correção operacional sem dupla contagem.

## Cenários de validação

- Consolidar MRR, churn, inadimplência e recuperação em um snapshot único.
- Segmentar desempenho por coorte e por canal.
- Abrir drill-down de um indicador e localizar a composição operacional correspondente.
- Reconstruir um snapshot após mudança operacional e confirmar consistência.
- Consultar painel executivo e endpoint de inspeção com filtros reaproveitáveis.

## Critérios para avançar à implementação completa

- regras centrais de contagem e segmentação documentadas
- snapshots reconstruíveis sem dupla contagem
- drill-down consistente com módulos `011` a `013`
- eventos analíticos mínimos definidos para o backbone `010`
- rollback operacional documentado para rebuild e revalidação

## Evidência de validação executada

- `git diff --check`
- `vendor/bin/pint --dirty --format=agent`
- `php artisan test --compact tests/Feature/PlatformCommercialAnalyticsFoundationTest.php tests/Feature/PlatformCommercialAnalyticsSnapshotTest.php tests/Feature/PlatformCommercialAnalyticsDashboardTest.php tests/Feature/PlatformCommercialAnalyticsCohortTest.php tests/Feature/PlatformCommercialAnalyticsChannelTest.php tests/Feature/PlatformCommercialAnalyticsDrilldownTest.php tests/Feature/PlatformCommercialAnalyticsRiskInsightTest.php tests/Unit/PlatformCommercialAnalyticsRulesTest.php tests/Unit/PlatformCommercialAnalyticsDrilldownRulesTest.php tests/Unit/PlatformCommercialAnalyticsSegmentationTest.php`
- suíte completa: `360 passed`, `1 skipped`, `1685 assertions`
