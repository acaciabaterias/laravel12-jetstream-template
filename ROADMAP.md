# Roadmap do ERP BateriaExpert

## Status Atual (v1.2.0 - pronto para lançamento assistido com billing e payments centrais)

✅ 12 módulos implementados (`001` a `012`)
✅ 5 microserviços scaffoldados (`MS-001` a `MS-005`)
✅ suíte principal estabilizada com `331` testes passando e `1576` assertions
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

## Próximo módulo sugerido (v1.3.0)

- [ ] Módulo `013` de platform revenue recovery
- [ ] Régua de cobrança e dunning multicanal
- [ ] Escalonamento comercial para inadimplência persistente
- [ ] Automação de reengajamento após falhas de pagamento e chargeback

## Fase 1: Estabilização e Produção (v1.3.0)

- [x] Subir ambiente com Docker e validar integração ponta a ponta
- [x] Configurar Supabase para tenants reais
- [x] Implementar autenticação real entre ERP Core e microserviços
- [ ] Consolidar monitoramento com Prometheus e Grafana no backbone `010`
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
