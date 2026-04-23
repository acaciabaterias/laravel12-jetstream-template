# Roadmap

## Visao Geral

O projeto ja tem base arquitetural, modulos core, microservicos, documentacao operacional e stack Docker versionados. Os proximos passos priorizam execucao integrada, endurecimento operacional e entrada em producao.

## Fase 1: First Boot Integrado

- subir o stack completo com `docker compose up --build -d`
- validar ERP Core, worker, scheduler e microservicos
- executar smoke tests operacionais
- validar migrations centrais e fluxo de provisionamento tenant

## Fase 2: Homologacao

- validar fluxos ponta a ponta dos modulos `001` a `009`
- validar integracoes mock e reais dos microservicos
- revisar filas, retries, timeouts e healthchecks
- revisar backups e restore em ambiente controlado

## Fase 3: Hardening de Producao

- revisar segredos, rotacao e armazenamento seguro
- endurecer configuracoes de Nginx, Supervisor, Redis e PostgreSQL
- revisar politicas de observabilidade, logs e alertas
- finalizar estrategia de deploy em Proxmox e Supabase

## Fase 4: Go-Live

- publicar primeira release versionada
- executar checklist de rollout
- acompanhar desempenho e incidentes iniciais
- fechar backlog de ajustes de onboarding e suporte

## Backlog Estrategico

- cobertura automatizada adicional para execucao integrada com containers
- dashboards operacionais e alertas de negocio
- runbooks de incidentes por modulo e por microservico
- refinamento de CI/CD para deploy automatizado
- documentacao externa para clientes e parceiros
