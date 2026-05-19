# Quickstart: Módulo 022 - Multi-Currency Support

## Objetivo

Validar o fluxo central de múltiplas moedas cobrindo preferência monetária por operador, publicação governada de moedas e taxas, inspeção de inconsistências e rollback da última tabela saudável.

## Pré-requisitos

- banco central configurado
- autenticação administrativa do módulo `002` operacional
- backbone `010` disponível para eventos materiais
- módulos centrais `011` a `021` disponíveis para renderização de valores monetários base

## Sequência sugerida

1. Autenticar um operador de plataforma no painel administrativo.
2. Publicar uma release monetária com `BRL`, `USD` e `EUR`, definindo moeda base e moeda padrão.
3. Alterar a preferência monetária do operador para `USD`.
4. Recarregar o dashboard central e validar a mudança de moeda de exibição.
5. Consultar a inspeção central de currencies.
6. Registrar uma publicação degradada ou com taxa inconsistente.
7. Executar rollback para a publicação anterior saudável.
8. Confirmar os eventos materiais no backbone `010`.

## Cenários de validação

- Preferência inválida deve cair na moeda padrão ativa.
- Publicação sem taxa válida para moeda suportada deve ser bloqueada ou marcada como inconsistente.
- Inconsistência material deve gerar relatório inspecionável.
- Rollback deve restaurar a última publicação saudável sem apagar preferências dos operadores.

## Critérios para avançar ao fechamento

- moeda resolvida por operador no plano central
- publicação governada com snapshot de taxas por moeda
- inspeção JSON retornando publicações, taxas e inconsistências
- rollback auditável restaurando a última publicação saudável
- runbook operacional cobrindo publicação, fallback monetário e reversão

## Evidência de validação

- suíte focal do módulo `022`: `15` testes passando com `60` assertions
- suíte completa em PostgreSQL: `481` testes passando, `1` skipped e `2526` assertions
- validação executada com `DB_CONNECTION=central` e bancos PostgreSQL temporários para central e tenant
