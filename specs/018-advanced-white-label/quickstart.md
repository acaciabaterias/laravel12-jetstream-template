# Quickstart: Módulo 018 - Advanced White Label Experience

## Objetivo

Validar localmente a camada central de branding, tema versionado e rollback visual após a consolidação dos módulos `010` a `017`.

## Pré-requisitos

- banco central configurado
- catálogo central de tenants operacional
- backbone `010` operacional
- shell administrativo existente estável
- tenants elegíveis para white label definidos

## Sequência sugerida

1. Registrar identidade visual base para um tenant piloto.
2. Cadastrar ativos principais e tokens obrigatórios do tema.
3. Salvar uma versão draft do tema com preferências de navegação.
4. Executar validação mínima de contraste e completude.
5. Publicar a versão aprovada para o tenant piloto.
6. Consultar inspeção central de branding e confirmar versão ativa.
7. Executar rollback controlado para a última versão saudável.
8. Confirmar publicação dos eventos materiais no backbone `010`.

## Cenários de validação

- Diferenciar identidade draft, ativa e arquivada.
- Bloquear tema sem tokens obrigatórios ou contraste mínimo.
- Publicar versão válida com registro auditável do operador.
- Restaurar versão saudável via rollback visual controlado.
- Consultar inspeção reutilizável de branding, versões e histórico.

## Critérios para avançar à implementação completa

- ao menos um tenant piloto com identidade visual centralizada
- validação mínima impedindo publicação inconsistente
- histórico de versão e rollback auditável por tenant
- fallback seguro para shell administrativo e ativos críticos
- runbook operacional cobrindo publicação e reversão

## Evidência de validação esperada

- `git diff --check`
- testes direcionados de branding, publicação e rollback
- evidência de publicação válida e rollback registrada nos artefatos do módulo

## Evidência executada

- `git diff --check`
- `php artisan test --compact tests/Unit/AdvancedWhiteLabelThemeTokenRulesTest.php tests/Unit/AdvancedWhiteLabelPublicationRulesTest.php tests/Unit/AdvancedWhiteLabelRollbackRulesTest.php`
- `php artisan test --compact` com `central` e `tenant` em PostgreSQL
- `vendor/bin/pint --dirty --format agent`

Resultado consolidado do recorte:

- unitário do módulo `018`: `4 passed`, `7 assertions`
- suíte completa PostgreSQL após a implementação do módulo: `421 passed`, `1 skipped`, `2256 assertions`
- publicação válida, bloqueio por contraste insuficiente e rollback com restauração auditável confirmados no dashboard `/admin/branding` e na inspeção `/admin/branding/inspection`
