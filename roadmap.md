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
- [x] `011` Platform billing control plane
- [x] `012` Platform payments and reconciliation
- [x] `013` Platform revenue recovery
- [x] `014` Platform commercial analytics

Este bloco consolidou o que estava fora dos módulos funcionais `001-009`:

- contratos canônicos de eventos entre ERP e microserviços
- publicação e consumo confiável via broker com rastreabilidade
- outbox/inbox, retries, replay e dead-letter operacional
- API Gateway para integrações síncronas controladas
- métricas, dashboards e trilha operacional ponta a ponta
- catálogo central de planos e assinaturas SaaS
- grace period, bloqueio, reativação e trilha comercial auditável
- painel administrativo e inspeção central da saúde comercial
- emissão externa de cobranças SaaS, webhooks idempotentes e conciliação central
- replay operacional de retornos, fila de exceções e inspeção financeira central
- régua de cobrança, escalonamento, promessas e inspeção central de recuperação de receita
- snapshots executivos de MRR, churn, inadimplência e recuperação
- recortes por coorte, canal e carteira com drill-down analítico reutilizável

## Próxima sequência sugerida

### Fase 1: Analytics comercial da plataforma
- [x] Especificar `014-platform-commercial-analytics`
- [x] Consolidar métricas executivas de MRR, recuperação e churn
- [x] Implementar visão analítica por coorte, carteira e canal

### Fase 2: Produção assistida
- [x] Especificar `015-production-observability-assurance`
- [ ] Validar monitoramento Prometheus/Grafana com cenários reais
- [ ] Executar testes de carga nas integrações críticas
- [ ] Formalizar runbooks de replay, contingência e recuperação
