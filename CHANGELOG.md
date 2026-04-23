# Changelog

Todas as mudancas relevantes deste projeto devem ser documentadas aqui.

Este arquivo foi consolidado a partir do historico recente de commits e segue o estilo de resumo do Keep a Changelog.

## [Unreleased]

### Added

- documentacao expandida de arquitetura, microservicos, API, deploy, troubleshooting, performance e templates de colaboracao

### Planned

- validacao completa do stack Docker em ambiente integrado
- endurecimento final de producao para ERP Core e microservicos
- ajustes operacionais para rollout inicial

## [0.9.0] - 2026-04-23

### Added

- implementacao do ERP Core cobrindo modulos `001` a `009`
- scaffold e consolidacao dos microservicos `MS-001` a `MS-005`
- migrations canonicas para banco central e tenant
- requests, factories, jobs, events, listeners, middlewares, casts e comandos Artisan
- macros, excecoes customizadas, mailables, seeders e gates/policies
- scripts operacionais, workflows e guias de deploy
- especificacao OpenAPI, collection Postman e README consolidado

### Changed

- alinhamento geral do codigo, specs e operacao com a Constitution `v2.0.0`
- consolidacao do modelo tenant-aware baseado em `database-per-client`

### Docs

- ampliacao da documentacao tecnica e operacional do monorepo

## [0.8.0] - 2026-04-22

### Changed

- refatoracao dos modulos `001` a `009` e dos microservicos para aderencia a Constitution `v2.0.0`
- remocao de premissas legadas de isolamento logico por filial

## [0.7.0] - 2026-04-17

### Added

- conclusao do modulo de cadastros estruturais
- estabilizacao da suite de testes naquele marco
- consolidacao do modulo `001` de multi-tenancy

## [0.6.0] - 2026-04-16

### Added

- baseline arquitetural completa do ERP BateriaExpert
- refinamento das especificacoes de microservicos com Constitution Checks

### Changed

- evolucao do modulo `001` para o modelo SaaS com suporte a white-label

## [0.5.0] - 2026-04-14

### Added

- milestone inicial das especificacoes de cadastros estruturais com issues criticas resolvidas

## [0.1.0] - 2026-03-04

### Added

- bootstrap do repositorio
- baseline inicial de pacotes e estrutura do projeto
