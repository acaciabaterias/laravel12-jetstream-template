# Roadmap de Implementação - ERP Baterias

## Status consolidado

- [x] `001` Multi-Tenancy isolado
- [x] `002` Usuários e perfis / RBAC
- [x] `003` Cadastros estruturais
- [x] `004` Estoque e logística reversa
- [x] `005` Vendas, vales e OS
- [x] `006` Logística e app do entregador
- [x] `007` Garantias e feedback
- [x] `008` Financeiro inteligente
- [x] `009` Orquestração fiscal e bancária

## Lacuna transversal fechada

- [x] `010` Backbone de integração e observabilidade

Este bloco consolidou o que estava fora dos módulos funcionais `001-009`:

- contratos canônicos de eventos entre ERP e microserviços
- publicação e consumo confiável via broker com rastreabilidade
- outbox/inbox, retries, replay e dead-letter operacional
- API Gateway para integrações síncronas controladas
- métricas, dashboards e trilha operacional ponta a ponta

## Próxima sequência sugerida

### Fase 1: Billing control plane
- [ ] Especificar `011-platform-billing-control-plane`
- [ ] Planejar catálogo de planos, assinaturas, faturas SaaS e política de bloqueio
- [ ] Gerar tarefas e executar a implementação incremental

### Fase 2: Produção assistida
- [ ] Validar monitoramento Prometheus/Grafana com cenários reais
- [ ] Executar testes de carga nas integrações críticas
- [ ] Formalizar runbooks de replay, contingência e recuperação
