# BateriaExpert ERP

[![Tests Passing](https://img.shields.io/badge/tests-347%20passed-brightgreen)](#testes)
[![Coverage](https://img.shields.io/badge/coverage-pending-lightgrey)](#testes)
[![PHP Version](https://img.shields.io/badge/php-8.3-777bb4)](#stack)
[![Laravel Version](https://img.shields.io/badge/laravel-12-ff2d20)](#stack)
[![License: MIT](https://img.shields.io/badge/license-MIT-green)](./composer.json)
[![Docker Ready](https://img.shields.io/badge/docker-ready-2496ed)](#rodando-com-docker)
[![Kubernetes Ready](https://img.shields.io/badge/kubernetes-ready-326ce5)](#kubernetes-deploy-checklist)
[![GitHub Stars](https://img.shields.io/github/stars/acaciabaterias/laravel12-jetstream-template?style=social)](https://github.com/acaciabaterias/laravel12-jetstream-template)

ERP especializado para distribuidores, revendas e operacoes de baterias automotivas, com arquitetura `database-per-client`, backoffice SaaS central e microservicos dedicados para fiscal, bancario, notificacoes, Open Finance e geocoding.

## Visao Geral

O BateriaExpert foi estruturado como um monorepo com:

- ERP Core em Laravel 12
- Autenticacao e UI com Jetstream, Livewire e Volt
- Banco central para catalogo SaaS, assinaturas e provisionamento
- Bancos isolados por tenant/CNPJ
- Microservicos independentes para integracoes especializadas

Os modulos core cobrem:

- multi-tenancy isolado
- RBAC
- cadastros estruturais
- estoque e logistica reversa
- vendas, pedidos e OS
- logistica e entregas
- garantias e feedback
- financeiro inteligente
- orquestracao fiscal e bancaria
- backbone de integracao e observabilidade
- billing control plane central
- pagamentos SaaS e reconciliacao central
- recuperacao de receita e dunning central

## Arquitetura

```mermaid
flowchart LR
    U[Usuarios ERP e Plataforma]
    A[ERP Core<br/>Laravel 12 + Livewire]
    C[(Banco Central<br/>PostgreSQL)]
    T[(Banco Tenant<br/>PostgreSQL / Supabase)]

    MS1[MS-001 Fiscal ACBr]
    MS2[MS-002 Bancario]
    MS3[MS-003 WhatsApp n8n]
    MS4[MS-004 Open Finance]
    MS5[MS-005 Geocoding]

    R[(Redis / Queues)]
    W[Workers / Jobs]

    U --> A
    A --> C
    A --> T
    A --> R
    R --> W

    A <--> MS1
    A <--> MS2
    A <--> MS3
    A <--> MS4
    A <--> MS5

    MS1 --> R
    MS2 --> R
    MS3 --> R
    MS4 --> R
    MS5 --> R
```

## Backbone de Integracao

O modulo `010` adiciona a espinha dorsal assíncrona entre ERP e microservicos:

- `evento_outboxes` para publicação confiável
- `evento_inboxes` para consumo idempotente
- `entregas_integracao` para retries, dead-letter e replay
- `contratos_evento` e `endpoints_integracao` para governança operacional
- dashboard `/integration/backbone` e API `/api/integration/inspections` para operação

## Payments Control Plane

O modulo `012` fecha o ciclo financeiro externo do SaaS:

- emissão de cobranças externas vinculadas a `FaturaSaaS`
- webhooks e retornos idempotentes
- conciliação automática segura
- replay manual de retornos em `platform-payments:replay-return`
- dashboard `/admin/payments` e inspeção `/admin/payments/inspection`

## Revenue Recovery Control Plane

O modulo `013` adiciona a camada central de recuperação de receita:

- abertura de casos a partir de atraso ou falha de cobrança
- deduplicação por estágio e canal
- escalonamento humano de casos críticos
- promessas de pagamento com suspensão seletiva das ações automáticas
- dashboard `/admin/recovery`, operação `/admin/recovery/operacoes` e inspeção `/admin/recovery/inspection`

## Stack

- PHP `^8.3`
- Laravel `12`
- Livewire `4`
- Volt
- Tailwind CSS `4`
- PostgreSQL `15+`
- Redis
- Vite
- Docker Compose para ambiente integrado

## Pre-requisitos

Para desenvolvimento local sem Docker:

- PHP `8.3+`
- Composer `2+`
- Node.js `20+`
- npm
- PostgreSQL `15+`
- Redis

Para ambiente integrado:

- Docker
- Docker Compose

## Instalacao Local

### 1. Clonar o repositorio

```bash
git clone <seu-repo>.git
cd laravel12-jetstream-template
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Configurar ambiente

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar PostgreSQL central

Use o guia:

- [POSTGRESQL_LOCAL_SETUP.md](./POSTGRESQL_LOCAL_SETUP.md)

Depois valide com:

```bash
./check-pg.sh
```

### 5. Ajustar `.env`

Exemplo minimo para o banco central:

```dotenv
DB_CONNECTION=central
DB_CENTRAL_DRIVER=pgsql
DB_CENTRAL_HOST=localhost
DB_CENTRAL_PORT=5432
DB_CENTRAL_DATABASE=erp_central
DB_CENTRAL_USERNAME=gil
DB_CENTRAL_PASSWORD=sua_senha
```

### 6. Rodar migrations centrais

```bash
php artisan migrate --database=central --path=database/migrations/central --no-interaction
```

### 7. Popular dados iniciais

```bash
php artisan db:seed --class=PlanosSeeder --no-interaction
php artisan db:seed --class=SuperAdminSeeder --no-interaction
```

### 8. Subir a aplicacao

```bash
composer run dev
```

Se preferir processos separados:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

## 📚 Documentação

- **[README.md](README.md)** - Visão geral do projeto
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Arquitetura do sistema e diagramas
- **[DATABASE.md](DATABASE.md)** - Modelo de dados e relacionamentos
- **[API_GUIDE.md](API_GUIDE.md)** - Guia de uso da API
- **[MICROSERVICES.md](MICROSERVICES.md)** - Detalhamento dos microserviços
- **[FAQ.md](FAQ.md)** - Perguntas frequentes
- **[CONTRIBUTING.md](CONTRIBUTING.md)** - Guia para contribuidores
- **[CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md)** - Código de conduta
- **[SECURITY.md](SECURITY.md)** - Política de segurança
- **[SUPPORT.md](SUPPORT.md)** - Canais de suporte
- **[CHANGELOG.md](CHANGELOG.md)** - Histórico de versões
- **[ROADMAP.md](ROADMAP.md)** - Roadmap do projeto
- **[RELEASE_PROCESS.md](RELEASE_PROCESS.md)** - Processo de release
- **[GO_LIVE_RUNBOOK.md](GO_LIVE_RUNBOOK.md)** - Runbook de go-live e rollback
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Solução de problemas

## Testes

Rodar a suite completa:

```bash
php artisan test --compact
```

Rodar um arquivo especifico:

```bash
php artisan test --compact tests/Feature/SalesServiceOsTest.php
```

Formatacao:

```bash
vendor/bin/pint --dirty --format agent
```

### Documentação da API (OpenAPI/Swagger)

A documentação interativa da API está disponível em [/api/docs](/api/docs) (requer autenticação).

## Testes de carga com K6

Os cenarios K6 ficam em `tests/k6/`:

- `load-test-create-vale.js`: autentica na aplicacao, abre o `dashboard`, executa `createVale` e `addItem` via `POST /livewire/update`
- `load-test-concurrent-users.js`: simula `100` usuarios simultaneos navegando por home, login e dashboard autenticado
- `smoke-test.js`: valida rapidamente home, login e dashboard; opcionalmente cria um vale simples

Variaveis de ambiente suportadas:

```bash
export BASE_URL=http://127.0.0.1:8000
export USER_EMAIL=vendedor.demo@bateriaexpert.test
export USER_PASSWORD=password
```

Executar o smoke test:

```bash
k6 run tests/k6/smoke-test.js
```

Executar o smoke test com criacao de vale:

```bash
SMOKE_CREATE_VALE=true k6 run tests/k6/smoke-test.js
```

Executar o teste de carga de criacao de vales:

```bash
k6 run tests/k6/load-test-create-vale.js
```

Executar o teste de concorrencia com `100` usuarios:

```bash
k6 run tests/k6/load-test-concurrent-users.js
```

Se a aplicacao estiver servindo frontend e backend separadamente, garanta que `php artisan serve` e `npm run dev` ou `composer run dev` estejam ativos antes de rodar os cenarios.

### Rate Limiting Multi-tenant

O sistema aplica limites de requisições baseados no plano do cliente para garantir estabilidade e previsibilidade.

| Plano | Limite (Req/min) |
| :--- | :--- |
| **Free** | 60 |
| **Pro** | 600 |
| **Enterprise** | 6000 |

#### Cabeçalhos HTTP
As requisições retornam cabeçalhos informativos:
- `X-RateLimit-Limit`: Limite total permitido.
- `X-RateLimit-Remaining`: Requisições restantes na janela atual.
- `X-RateLimit-Reset`: Timestamp Unix de quando o limite será resetado.

#### Tratamento de Erros
Quando o limite é excedido, a API retorna `HTTP 429 Too Many Requests`. O cliente deve aguardar o tempo indicado no cabeçalho `Retry-After` (segundos) antes de tentar novamente.

#### Reset Manual
Administradores podem resetar os limites via Artisan:
```bash
# Reset para um tenant específico
php artisan tenant:ratelimit-reset --tenant=subdominio

# Reset global
php artisan tenant:ratelimit-reset --all
```

## Rodando com Docker

O `docker-compose.yml` da raiz sobe os microservicos scaffoldados:

- `MS-001 Fiscal`
- `MS-002 Bancario`
- `MS-003 WhatsApp n8n`
- `MS-004 Open Finance`
- `MS-005 Geocoding`

Subir o stack:

```bash
docker compose up -d --build
```

Se a porta `8000` ja estiver em uso, defina outra porta para o ERP Core:

```bash
ERP_CORE_HTTP_PORT=8080 docker compose up -d --build
```

Validar containers:

```bash
docker compose ps
```

Executar healthcheck:

```bash
./healthcheck.sh
```

## Scripts Operacionais

- [check-pg.sh](./check-pg.sh): verifica PostgreSQL local
- [backup.sh](./backup.sh): backup do banco central e opcionalmente de tenant
- [restore.sh](./restore.sh): restore a partir de dump PostgreSQL
- [healthcheck.sh](./healthcheck.sh): valida endpoints principais

## Documentacao

### API

- [openapi.yaml](./openapi.yaml)
- [postman_collection.json](./postman_collection.json)
- [API_GUIDE.md](./API_GUIDE.md)
- [MICROSERVICES.md](./MICROSERVICES.md)

### Deploy

- [DEPLOY_PROXMOX.md](./DEPLOY_PROXMOX.md)
- [DEPLOY_SUPABASE.md](./DEPLOY_SUPABASE.md)
- [DEPLOY_PRODUCAO.md](./DEPLOY_PRODUCAO.md)
- [DEPLOYMENT_DETAILED.md](./DEPLOYMENT_DETAILED.md)
- [GO_LIVE_RUNBOOK.md](./GO_LIVE_RUNBOOK.md)

### Operacao

- [TROUBLESHOOTING.md](./TROUBLESHOOTING.md)
- [PERFORMANCE.md](./PERFORMANCE.md)

### Governanca

- [CONTRIBUTING.md](./CONTRIBUTING.md)
- [CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md)
- [SECURITY.md](./SECURITY.md)
- [SUPPORT.md](./SUPPORT.md)
- [CHANGELOG.md](./CHANGELOG.md)

### Banco de dados

- [database/schema/central_postgres.sql](./database/schema/central_postgres.sql)
- [database/schema/tenant_postgres.sql](./database/schema/tenant_postgres.sql)
- [database/schema/tenant_rls_policies.sql](./database/schema/tenant_rls_policies.sql)

## GitHub Actions

Workflows incluidos em [`.github/workflows`](./.github/workflows):

- `test.yml`: executa PHPUnit, build Vite, valida Compose e build da imagem Docker do ERP Core
- `lint.yml`: valida Pint, `php -l` e a collection Postman
- `deploy.yml`: valida o bundle e prepara deploy manual por ambiente
- `deploy-k8s.yml`: aplica manifests Kubernetes por ambiente com smoke test

## Estrutura do Monorepo

```text
app/                      ERP Core
database/migrations/      Migracoes legadas e operacionais
database/migrations/central
database/migrations/tenant
database/schema/          Snapshots SQL canônicos
microservicos/
  ms-001-fiscal-acbr/
  ms-002-bancario/
  ms-003-whatsapp-n8n/
  ms-004-openfinance/
  ms-005-geocoding/
.github/workflows/        CI/CD
```

## Ponto de Entrada para Desenvolvimento

Se voce esta chegando agora no projeto, a ordem recomendada e:

1. Ler este README
2. Configurar PostgreSQL com [POSTGRESQL_LOCAL_SETUP.md](./POSTGRESQL_LOCAL_SETUP.md)
3. Rodar `./check-pg.sh`
4. Aplicar migrations centrais
5. Rodar `php artisan test --compact`
6. Consultar [openapi.yaml](./openapi.yaml) e a collection Postman
7. Usar os guias de deploy conforme o ambiente alvo

## Licenca

Este projeto utiliza licenca MIT. Consulte o metadata em [composer.json](./composer.json).
