# Containerization Checklist

## Objetivo

Este checklist consolida o estado atual da containerizacao do monorepo `BateriaExpert` e mostra o que precisa estar pronto antes de subir o ambiente completo com Docker.

Escopo avaliado:

- ERP Core
- Microservicos `MS-001` a `MS-005`
- Arquivos base de infraestrutura
- Validacao minima de ambiente

## Status Atual

| Item | Status | Observacao |
|------|--------|------------|
| `Dockerfile` do ERP Core | `MISSING` | Nao existe `Dockerfile` na raiz para a aplicacao Laravel principal |
| `docker-compose.yml` da raiz | `PARTIAL` | Ja sobe os microservicos, mas ainda nao sobe o ERP Core, Redis do ERP, banco central do ERP, worker e scheduler |
| `.env.example` da raiz | `PARTIAL` | Existe, mas ainda esta com defaults de desenvolvimento local (`sqlite`) e sem foco completo em Docker/PostgreSQL |
| `entrypoint.sh` do ERP Core | `MISSING` | Nao existe script de bootstrap para install/cache/migrate/start |
| `healthcheck.sh` | `OK` | Existe e valida ERP + microservicos por HTTP |
| `validate-env.sh` | `MISSING` | Ainda nao existe validador de variaveis obrigatorias antes do `docker compose up` |
| `Dockerfile` dos microservicos | `OK` | Presente em `microservicos/ms-001` a `ms-005` |
| `.env.example` dos microservicos | `OK` | Presente em `microservicos/ms-001` a `ms-005` |

## Checklist Final

### 1. ERP Core

- [ ] Criar `Dockerfile` na raiz para o Laravel 12
- [ ] Criar `entrypoint.sh` para bootstrap do ERP Core
- [ ] Incluir `php-fpm` ou `artisan serve` somente se for ambiente estritamente local
- [ ] Garantir permissao de escrita em `storage/` e `bootstrap/cache/`
- [ ] Definir estrategia de install:
  - `composer install --no-interaction --prefer-dist`
  - cache de config/route/view
  - chave `APP_KEY`

### 2. Docker Compose da Raiz

- [ ] Adicionar servico `erp_core_app`
- [ ] Adicionar servico `erp_core_db` com PostgreSQL 15+
- [ ] Adicionar servico `erp_core_redis`
- [ ] Adicionar servico `erp_core_queue`
- [ ] Adicionar servico `erp_core_scheduler`
- [ ] Opcional: adicionar `nginx` ou proxy reverso
- [ ] Mapear volumes persistentes para:
  - banco central
  - `storage/`
  - logs

### 3. Banco e Migrations

- [ ] Executar migrations da central no startup controlado
- [ ] Separar claramente bootstrap da central e bootstrap de tenants
- [ ] Garantir que `tenant:migrate-all --force` nao rode automaticamente em ambientes indevidos
- [ ] Definir politica de provisionamento inicial do tenant fora do boot principal

### 4. Variaveis de Ambiente

- [ ] Atualizar `.env.example` da raiz para PostgreSQL
- [ ] Incluir variaveis de Redis
- [ ] Incluir variaveis da conexao central
- [ ] Incluir URLs internas dos microservicos
- [ ] Incluir flags operacionais:
  - `APP_ENV`
  - `APP_DEBUG`
  - `APP_URL`
  - `QUEUE_CONNECTION`
  - `CACHE_STORE`
  - `SESSION_DRIVER`
  - `MAINTENANCE_MODE`

### 5. Validacao e Observabilidade

- [ ] Criar `validate-env.sh`
- [ ] Validar presenca de variaveis obrigatorias antes do `up`
- [ ] Validar se `APP_KEY` esta configurada
- [ ] Validar conectividade com Postgres e Redis
- [ ] Reusar `healthcheck.sh` no fluxo de verificacao pos-subida

## O Que Ja Esta Pronto

### Microservicos

- [x] `MS-001 Fiscal ACBr`
- [x] `MS-002 Bancario`
- [x] `MS-003 WhatsApp / n8n`
- [x] `MS-004 Open Finance`
- [x] `MS-005 Geocoding`

Todos ja possuem:

- `Dockerfile`
- `.env.example`
- scaffold de API
- stack base no `docker-compose.yml`

### Scripts e Documentacao

- [x] `healthcheck.sh`
- [x] `backup.sh`
- [x] `restore.sh`
- [x] `check-pg.sh`
- [x] `DEPLOY_PROXMOX.md`
- [x] `DEPLOY_SUPABASE.md`
- [x] `DEPLOY_PRODUCAO.md`
- [x] `README.md`

## Gaps Criticos Antes do Primeiro `docker compose up`

Os pontos abaixo ainda bloqueiam uma subida completa do ambiente do ERP:

1. Falta `Dockerfile` da aplicacao principal.
2. Falta `entrypoint.sh` da aplicacao principal.
3. Falta `validate-env.sh`.
4. O `docker-compose.yml` da raiz ainda nao sobe o ERP Core.
5. O `.env.example` da raiz ainda nao esta ajustado para PostgreSQL + Redis + servicos Docker.

## Definicao de Pronto

Considere o ambiente pronto para subir quando todos os itens abaixo estiverem marcados:

- [ ] `Dockerfile` da raiz criado
- [ ] `entrypoint.sh` criado
- [ ] `validate-env.sh` criado
- [ ] `.env.example` ajustado para Docker
- [ ] `docker-compose.yml` com ERP Core + central DB + Redis + worker + scheduler
- [ ] `healthcheck.sh` cobrindo todos os endpoints esperados
- [ ] `docker compose config` sem erros
- [ ] `php artisan test --compact` verde

## Proximo Passo Recomendado

Para fechar a containerizacao, a ordem ideal e:

1. Criar `Dockerfile` do ERP Core
2. Criar `entrypoint.sh`
3. Criar `validate-env.sh`
4. Atualizar `.env.example`
5. Expandir o `docker-compose.yml` da raiz
6. Rodar `docker compose config`
7. Subir o ambiente e executar `healthcheck.sh`
