# Roadmap do ERP BateriaExpert

## Status Atual (v1.6.0 - pronto para lançamento assistido com observabilidade e monitoring backbone consolidados)

✅ 16 módulos implementados (`001` a `016`)
✅ 5 microserviços scaffoldados (`MS-001` a `MS-005`)
✅ suíte principal estabilizada com `396` testes passando, `1 skipped` e `2172` assertions
✅ Containerização completa (`Dockerfile`, `docker-compose.yml`)
✅ Documentação consolidada: OpenAPI, Postman, `ARCHITECTURE`, guias operacionais e governança
✅ RBAC completo (`15+` policies, gates)
✅ Seeders de demonstração

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

## Próximo bloco sugerido (v1.7.0)

- [ ] Executar teste de carga e otimização de queries nas integrações críticas
- [ ] Consolidar baseline operacional com evidência reproduzível por fluxo
- [ ] Expandir readiness para capacity planning e tuning preventivo

## Fase 1: Estabilização e Produção (v1.4.0)

- [x] Subir ambiente com Docker e validar integração ponta a ponta
- [x] Configurar Supabase para tenants reais
- [x] Implementar autenticação real entre ERP Core e microserviços
- [x] Consolidar monitoramento com Prometheus e Grafana no backbone `010`
- [ ] Executar teste de carga e otimização de queries nas integrações críticas

## Fase 2: Expansão Comercial (v1.3.0)

- [ ] White label avançado com temas customizáveis
- [ ] Dashboard analítico para Super Admin
- [ ] Relatórios avançados com exportação Excel e PDF
- [ ] Automação avançada de cobrança e recuperação de receita

## Fase 3: Internacionalização (v2.0.0)

- [ ] Suporte a múltiplos idiomas (`pt-BR`, `en`, `es`)
- [ ] Regras fiscais e CFOPs para exportação e importação
- [ ] Suporte a múltiplas moedas

## Fase 4: IA e Automação (v3.0.0)

- [ ] Previsão de demanda com machine learning
- [ ] Otimização de rotas com modelos preditivos
- [ ] Chatbot de suporte integrado
