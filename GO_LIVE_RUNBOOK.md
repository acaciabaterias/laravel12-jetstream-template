# Runbook de Go-Live e Rollback - ERP BateriaExpert

## Objetivo

Executar o deploy de producao com uma sequencia verificavel, cobrindo preparacao, backup, publicacao, validacao, decisao de go/no-go e rollback.

Use este runbook junto com:

- [DEPLOY_PRODUCAO.md](./DEPLOY_PRODUCAO.md)
- [DEPLOYMENT_DETAILED.md](./DEPLOYMENT_DETAILED.md)
- [BACKUP_GUIDE.md](./BACKUP_GUIDE.md)
- [POST_DEPLOY_CHECKLIST.md](./POST_DEPLOY_CHECKLIST.md)

## Dados do Deploy

Preencha antes de iniciar:

```text
Ambiente:
Versao/tag:
Commit:
Janela:
Responsavel tecnico:
Responsavel negocio:
Canal operacional:
Plano de rollback aprovado: sim/nao
Backup pre-deploy confirmado: sim/nao
```

## 1. Pre-Flight

Execute no workspace da versao candidata:

```bash
git status --short
composer install --no-interaction --prefer-dist --optimize-autoloader
npm ci
vendor/bin/pint --dirty --format agent
php artisan test --compact
npm run build
docker compose config --quiet
```

Valide o ambiente de producao ou os secrets equivalentes:

```bash
./validate-env.sh
```

Condicoes obrigatorias:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` com `https://`
- `APP_KEY` preservada e em formato `base64:`
- `SESSION_SECURE_COOKIE=true`
- `SESSION_ENCRYPT=true`
- `CORS_ALLOWED_ORIGINS` sem `*`
- `SUPER_ADMIN_PASSWORD` forte e nao-placeholder
- URLs reais dos microservicos configuradas

## 2. Backup Antes do Deploy

Gere backup do banco central:

```bash
export BACKUP_DIR=./backups/pre-go-live
./backup.sh
```

Se houver tenants criticos, gere backup de cada tenant antes de migrations:

```bash
export TENANT_DB_HOST=db.tenant.example
export TENANT_DB_NAME=tenant_empresa_001
export TENANT_DB_USER=postgres
export TENANT_DB_PASSWORD=senha_tenant
./backup.sh
```

Registre hash dos dumps:

```bash
sha256sum backups/pre-go-live/*.dump > backups/pre-go-live/SHA256SUMS
```

No-go imediato se:

- backup falhar
- dump tiver tamanho incoerente
- credenciais de restore nao estiverem disponiveis
- nao houver responsavel autorizado para rollback

## 3. Publicacao

### Aplicacao sem Docker

```bash
git fetch --all --prune
git checkout <tag-ou-commit>
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan down --render="errors::503" --retry=60
php artisan migrate --database=central --path=database/migrations/central --force --no-interaction
php artisan migrate --database=central --path=database/migrations/0001_01_01_000000_create_users_table.php --force --no-interaction
php artisan migrate --database=central --path=database/migrations/0001_01_01_000001_create_cache_table.php --force --no-interaction
php artisan migrate --database=central --path=database/migrations/0001_01_01_000002_create_jobs_table.php --force --no-interaction
php artisan db:seed --class=PlanosSeeder --force --no-interaction
php artisan db:seed --class=SuperAdminSeeder --force --no-interaction
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up
```

Se houver alteracoes de schema tenant:

```bash
php artisan tenant:migrate-all --force
```

### Aplicacao com Docker Compose

```bash
docker compose pull
docker compose up -d --build
docker compose ps
```

Se a porta `8000` estiver ocupada:

```bash
ERP_CORE_HTTP_PORT=8080 docker compose up -d --build
```

### Aplicacao com Kubernetes

Renderize antes de aplicar:

```bash
kubectl kustomize infra/kubernetes/production >/tmp/bateriaexpert-k8s.yaml
```

Aplique e valide rollout:

```bash
kubectl apply -f infra/kubernetes/namespace.yaml
kubectl apply -k infra/kubernetes/production
K8S_NAMESPACE=bateriaexpert ./infra/kubernetes/production/verify-k8s.sh
```

## 4. Smoke Test

Valide saude tecnica:

```bash
curl -i "${APP_URL}/up"
./healthcheck.sh
php artisan tenant:health --json
php artisan queue:failed
```

Valide fluxos manuais minimos:

- login no backoffice admin
- login de usuario ERP
- dashboard principal
- listagem de tenants/filiais
- criacao de um Vale de teste em homologacao ou tenant controlado
- consulta de estoque
- dashboard financeiro
- logout

Com K6, quando aplicavel:

```bash
export BASE_URL="${APP_URL}"
k6 run tests/k6/smoke-test.js
```

## 5. Go/No-Go

Declare go quando:

- `/up` responde com sucesso
- healthchecks dos microservicos passam
- workers estao ativos
- logs nao mostram erro critico recorrente
- login e dashboards principais funcionam
- backup pre-deploy esta registrado
- monitoramento esta recebendo dados

Declare no-go e inicie rollback quando:

- ERP Core fica indisponivel
- login falha para perfis principais
- migrations deixam banco central ou tenant indisponivel
- filas acumulam falhas criticas
- fiscal, bancario ou financeiro quebra em fluxo essencial
- erro critico persiste por mais de 10 minutos

## 6. Rollback

### Rollback de Codigo

Sem Docker:

```bash
php artisan down --render="errors::503" --retry=60
git checkout <tag-ou-commit-anterior>
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up
```

Docker Compose:

```bash
docker compose down
git checkout <tag-ou-commit-anterior>
docker compose up -d --build
docker compose ps
```

Kubernetes:

```bash
kubectl rollout undo deployment/erp-core-web -n bateriaexpert
kubectl rollout undo deployment/erp-core-queue -n bateriaexpert
kubectl rollout undo deployment/erp-core-scheduler -n bateriaexpert
K8S_NAMESPACE=bateriaexpert ./infra/kubernetes/production/verify-k8s.sh
```

### Restore de Banco

Use somente se a falha envolver dados ou migrations irreversiveis.

Banco central:

```bash
./restore.sh backups/pre-go-live/central_erp_central_YYYYMMDD_HHMMSS.dump erp_central
```

Tenant:

```bash
./restore.sh backups/pre-go-live/tenant_tenant_empresa_001_YYYYMMDD_HHMMSS.dump tenant_empresa_001
```

Apos restore:

```bash
php artisan migrate:status
php artisan tenant:health --json
./healthcheck.sh
```

## 7. Registro Final

Publique no canal operacional:

```text
Deploy:
Versao/tag:
Commit:
Inicio:
Fim:
Responsavel:
Backup:
Healthcheck:
Smoke test:
Monitoramento:
Decisao: aprovado/revertido
Rollback executado: sim/nao
Observacoes:
```

## 8. Pos-Deploy

Durante os primeiros 30 minutos:

- acompanhar logs da aplicacao
- acompanhar logs dos workers
- acompanhar health dos microservicos
- conferir alertas ativos
- conferir fila de jobs falhados

Comandos uteis:

```bash
php artisan queue:failed
php artisan tenant:list --status=active
php artisan tenant:health --json
./healthcheck.sh
```
