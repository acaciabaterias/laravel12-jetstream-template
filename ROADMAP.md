# Roadmap do ERP BateriaExpert

## Status Atual (v2.0.0-beta - módulo 020 concluído)

✅ 20 módulos implementados (`001` a `020`)
✅ módulo `020` concluído com jornadas adaptativas, experimentos governados e rollback auditável
✅ 5 microserviços scaffoldados (`MS-001` a `MS-005`)
✅ suíte principal estabilizada com `455` testes passando, `1 skipped` e `2424` assertions
✅ Containerização completa (`Dockerfile`, `docker-compose.yml`)
✅ Documentação consolidada: OpenAPI, Postman, `ARCHITECTURE`, guias operacionais e governança
✅ RBAC completo (`15+` policies, gates)
✅ Seeders de demonstração

## Fundação do ERP concluída

- [x] Módulo `001` de multi-tenancy isolado com resolução tenant-aware
- [x] Módulo `002` de autenticação, usuários, perfis, RBAC e auditoria de acesso
- [x] Módulo `003` de cadastros estruturais com fabricantes, veículos, baterias e aplicações
- [x] Módulo `004` de estoque e logística reversa
- [x] Módulo `005` de vendas, vales e assistência técnica
- [x] Módulo `006` de logística e app de entregas
- [x] Módulo `007` de garantias e feedback
- [x] Módulo `008` de financeiro inteligente
- [x] Módulo `009` de orquestração fiscal e bancária

## Backbone e billing central concluídos

- [x] Módulo `010` de backbone de integração e observabilidade
- [x] Contratos de eventos versionados entre ERP e microserviços
- [x] Outbox/inbox, replay e dead-letter operacional
- [x] API Gateway para chamadas síncronas controladas
- [x] Dashboards e métricas ponta a ponta para integrações críticas
- [x] Módulo `011` de platform billing control plane
- [x] Catálogo de planos, assinaturas e faturas SaaS
- [x] Política central de grace period, bloqueio e reativação
- [x] Painel super admin de saúde comercial dos assinantes
- [x] Eventos comerciais integrados ao backbone `010`
- [x] Módulo `012` de platform payments and reconciliation
- [x] Emissão externa de cobranças SaaS vinculadas a `FaturaSaaS`
- [x] Webhooks idempotentes, baixa automática e conciliação central
- [x] Replay operacional de retornos e fila de exceções financeiras
- [x] Dashboard administrativo e inspeção JSON de pagamentos
- [x] Módulo `013` de platform revenue recovery
- [x] Abertura central de casos por atraso e falha de cobrança
- [x] Escalonamento humano e promessas de pagamento com trilha auditável
- [x] Dashboard e inspeção JSON de recuperação de receita
- [x] Módulo `014` de platform commercial analytics
- [x] Snapshots executivos de MRR, churn, inadimplência e recuperação
- [x] Recortes por coorte, canal e carteira com drill-down reutilizável
- [x] Dashboard central e inspeção JSON de analytics comercial
- [x] Módulo `015` de production observability assurance
- [x] Snapshots operacionais, baselines de carga, incidentes e runbooks auditáveis
- [x] Dashboard central e inspeção JSON de observabilidade operacional
- [x] Módulo `016` de backbone monitoring consolidation
- [x] Consolidação central de scrape health, alertas materiais e readiness do stack externo
- [x] Versionamento de dashboards, validação, rollback e evidências auditáveis de monitoring
- [x] Módulo `017` de critical integration load optimization
- [x] Cenários reproduzíveis de benchmark, baseline e comparação por fluxo crítico
- [x] Governança central de gargalos, tuning validado e rollback auditável de performance
- [x] Módulo `018` de advanced white label experience
- [x] Identidade visual centralizada, tema versionado e publicação auditável por tenant
- [x] Rollback visual governado com restauração segura do shell administrativo

## Executive reporting e automação avançada concluídos

- [x] Módulo `019` - Executive Reporting Hub
- [x] Dashboard executivo super admin com filtros reutilizáveis e drill-down operacional
- [x] Relatórios avançados com exportação Excel e PDF auditável
- [x] Histórico central e reexecução governada de relatórios executivos
- [x] Módulo `020` - Advanced Revenue Recovery Automation
- [x] Jornadas adaptativas com fallback, cooldown, supressão e dispatch idempotente
- [x] Publicação governada de políticas com experimento, holdout e trilha auditável
- [x] Inspeção central e rollback auditável para a última política saudável

## Fase 1: Estabilização e Produção (v1.4.0)

- [x] Subir ambiente com Docker e validar integração ponta a ponta
- [x] Configurar Supabase para tenants reais
- [x] Implementar autenticação real entre ERP Core e microserviços
- [x] Consolidar monitoramento com Prometheus e Grafana no backbone `010`
- [x] Executar teste de carga e otimização de queries nas integrações críticas

## Fase 2: Expansão Comercial (v1.3.0)

 - [x] White label avançado com temas customizáveis
- [x] Dashboard analítico para Super Admin
- [x] Relatórios avançados com exportação Excel e PDF
- [x] Automação avançada de cobrança e recuperação de receita

## Fase 3: Internacionalização (v2.0.0)

- [ ] Suporte a múltiplos idiomas (`pt-BR`, `en`, `es`)
- [ ] Regras fiscais e CFOPs para exportação e importação
- [ ] Suporte a múltiplas moedas

## Fase 4: IA e Automação (v3.0.0)

- [ ] Previsão de demanda com machine learning
- [ ] Otimização de rotas com modelos preditivos
- [ ] Chatbot de suporte integrado
