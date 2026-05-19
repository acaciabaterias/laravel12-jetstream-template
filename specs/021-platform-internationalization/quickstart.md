# Quickstart: Módulo 021 - Platform Internationalization

## Objetivo

Validar o fluxo central de internacionalização cobrindo preferência de idioma por operador, publicação governada de locales suportados, fallback, inspeção de cobertura e rollback.

## Pré-requisitos

- banco central configurado
- autenticação administrativa do módulo `002` operacional
- backbone `010` disponível para eventos materiais
- artefatos `lang/pt_BR.json`, `lang/en.json` e `lang/es.json` presentes

## Sequência sugerida

1. Autenticar um operador de plataforma no painel administrativo.
2. Publicar uma release de idiomas com `pt_BR`, `en` e `es`, definindo locale padrão e fallback.
3. Alterar a preferência de idioma do operador para `en`.
4. Recarregar o dashboard e validar a mudança visível de idioma.
5. Consultar a inspeção central de localization.
6. Registrar uma publicação degradada ou com lacunas.
7. Executar rollback para a publicação anterior saudável.
8. Confirmar os eventos materiais no backbone `010`.

## Cenários de validação

- Preferência inválida deve cair no fallback ativo.
- Publicação sem fallback válido deve ser bloqueada.
- Locale com lacunas obrigatórias deve gerar relatório inspecionável.
- Rollback deve restaurar a última publicação saudável sem apagar preferências dos operadores.

## Critérios para avançar ao fechamento

- locale resolvido por operador no plano central
- publicação governada com snapshot de cobertura por locale
- inspeção JSON retornando publicações e lacunas
- rollback auditável restaurando a última publicação saudável
- runbook operacional cobrindo publicação, fallback e reversão

## Evidência de validação

- suíte focal do módulo `021`: `9` testes passando com `33` assertions
- suíte completa em PostgreSQL: `466` testes passando, `1` skipped e `2466` assertions
- validação executada com `DB_CONNECTION=central` e bancos PostgreSQL temporários para central e tenant
