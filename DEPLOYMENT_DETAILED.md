# Deployment Detailed

## Objetivo

Este guia descreve um fluxo de deploy em producao para o ERP Core e para os microservicos do monorepo.

Ele complementa:

- [DEPLOY_PRODUCAO.md](./DEPLOY_PRODUCAO.md)
- [DEPLOY_PROXMOX.md](./DEPLOY_PROXMOX.md)
- [DEPLOY_SUPABASE.md](./DEPLOY_SUPABASE.md)

## Topologia Recomendada

Minimo recomendado:

- 1 host para ERP Core
- 1 PostgreSQL central
- 1 Redis
- 1 host ou grupo para microservicos
- 1 reverse proxy

Portas padrao do stack:

- ERP Core: `8000`
- MS-001: `8001`
- MS-002: `8002`
- MS-003: `8003`
- MS-004: `8004`
- MS-005: `8005`

## Checklist Pre-Deploy

Antes de iniciar:

- definir dominio e SSL
- separar segredos por ambiente
- validar acesso ao banco central
- validar Redis para filas
- validar conectividade entre ERP Core e microservicos
- preparar estrategia de backup e rollback

## Variaveis de Ambiente Criticas

ERP Core:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://erp.seudominio.com

DB_CONNECTION=central
DB_CENTRAL_DRIVER=pgsql
DB_CENTRAL_HOST=postgres-central
DB_CENTRAL_PORT=5432
DB_CENTRAL_DATABASE=erp_central
DB_CENTRAL_USERNAME=erp
DB_CENTRAL_PASSWORD=senha-forte

REDIS_HOST=redis
REDIS_PORT=6379
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=database

MS001_BASE_URL=http://ms-001-interno:8001
MS002_BASE_URL=http://ms-002-interno:8002
MS003_BASE_URL=http://ms-003-interno:8003
MS004_BASE_URL=http://ms-004-interno:8004
MS005_BASE_URL=http://ms-005-interno:8005
```

Cada microservico deve ter seu proprio `.env` com:

- `APP_ENV=production`
- `APP_DEBUG=false`
- conexao PostgreSQL local ao servico
- Redis local do servico, quando aplicavel
- chaves de integracao externas

## Passo a Passo do ERP Core

### 1. Atualizar codigo

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 2. Preparar aplicacao

```bash
php artisan key:generate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Se a chave ja existir em producao, nao gere outra. Nesse caso, mantenha a `APP_KEY` atual.

### 3. Rodar migrations do banco central

```bash
php artisan migrate --database=central --path=database/migrations/central --no-interaction --force
```

### 4. Rodar seeders centrais

```bash
php artisan db:seed --class=PlanosSeeder --no-interaction --force
php artisan db:seed --class=SuperAdminSeeder --no-interaction --force
```

### 5. Provisionar tenants

Se o ambiente exigir migracao dos bancos tenant:

```bash
php artisan tenant:migrate-all --force
```

Execute isso somente quando houver certeza de que o rollout exige alteracoes em schemas tenant.

### 6. Reiniciar filas e scheduler

Queue worker:

```bash
php artisan queue:restart
```

Scheduler:

- manter `php artisan schedule:run` via cron
- ou usar processo dedicado em loop controlado

### 7. Verificar saude

```bash
curl -i https://erp.seudominio.com/up
./healthcheck.sh
```

## Passo a Passo dos Microservicos

Repita para cada servico:

### 1. Atualizar codigo

```bash
cd microservicos/ms-001-fiscal-acbr
git pull origin main
composer install --no-dev --optimize-autoloader
```

### 2. Rodar migrations

```bash
php artisan migrate --no-interaction --force
```

### 3. Publicar processo

Opcoes comuns:

- `systemd`
- `supervisord`
- containers Docker

Exemplo `systemd`:

```ini
[Unit]
Description=MS-001 Fiscal
After=network.target

[Service]
User=www-data
WorkingDirectory=/srv/bateriaexpert/microservicos/ms-001-fiscal-acbr
ExecStart=/usr/bin/php artisan serve --host=0.0.0.0 --port=8001
Restart=always

[Install]
WantedBy=multi-user.target
```

### 4. Verificar health

```bash
curl -i http://127.0.0.1:8001/api/v1/health
curl -i http://127.0.0.1:8002/api/v1/health
curl -i http://127.0.0.1:8003/api/v1/health
curl -i http://127.0.0.1:8004/api/v1/health
curl -i http://127.0.0.1:8005/api/v1/health
```

## Deploy com Docker Compose

Se a operacao usar a stack descrita em `docker-compose.yml`:

### 1. Preparar arquivos

```bash
cp .env.example .env
cp microservicos/ms-001-fiscal-acbr/.env.example microservicos/ms-001-fiscal-acbr/.env
cp microservicos/ms-002-bancario/.env.example microservicos/ms-002-bancario/.env
cp microservicos/ms-003-whatsapp-n8n/.env.example microservicos/ms-003-whatsapp-n8n/.env
cp microservicos/ms-004-openfinance/.env.example microservicos/ms-004-openfinance/.env
cp microservicos/ms-005-geocoding/.env.example microservicos/ms-005-geocoding/.env
```

### 2. Subir stack

```bash
docker compose up -d --build
docker compose ps
```

### 3. Validar ambiente

```bash
./healthcheck.sh
```

## Reverse Proxy

O proxy deve:

- terminar SSL
- encaminhar ERP Core para `:8000`
- encaminhar microservicos para `:8001` a `:8005`
- opcionalmente normalizar rotas de gateway como `/ms-001/v1/...`

## Ordem Segura de Release

Em deploy manual, a sequencia mais segura costuma ser:

1. backup
2. pull do codigo
3. install de dependencias
4. build dos assets
5. migrate central
6. migrate tenant quando necessario
7. restart de queue workers
8. healthchecks
9. smoke tests de negocio

## Smoke Tests Recomendados

Depois do deploy:

```bash
curl -i https://erp.seudominio.com/up
curl -i http://ms-001.interno:8001/api/v1/health
curl -i http://ms-002.interno:8002/api/v1/health
curl -i http://ms-003.interno:8003/api/v1/health
curl -i http://ms-004.interno:8004/api/v1/health
curl -i http://ms-005.interno:8005/api/v1/health
```

Fluxos funcionais minimos:

- login no ERP
- consulta de tenant
- sincronizacao mobile
- emissao mock fiscal
- cobranca PIX
- envio de notificacao
- captura Open Finance
- otimizacao de rota

## Rollback

Plano minimo:

1. manter release anterior versionada
2. restaurar `.env` e build anterior, se necessario
3. reverter servicos para a versao anterior
4. restaurar banco somente quando houver mudanca incompatível de schema ou dados
5. reexecutar healthchecks

Evite rollback cego de banco apos migrations destrutivas sem plano de restauracao validado.

## Pos-Deploy

- revisar logs do ERP Core
- revisar logs dos microservicos
- acompanhar filas
- confirmar execucao do scheduler
- validar backup automatizado

## Referencias

- [README.md](./README.md)
- [MICROSERVICES.md](./MICROSERVICES.md)
- [API_GUIDE.md](./API_GUIDE.md)
- [TROUBLESHOOTING.md](./TROUBLESHOOTING.md)
