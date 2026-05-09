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

### Evidencias Operacionais (US1 Tenant Resolution / US2 Provisioning)

Preencher e versionar no mesmo PR/release:

```text
Data da evidencia:
Responsavel:

T038 - Backup pre-alteracao:
- Central: <arquivo dump + hash + horario>
- Tenant alvo/homolog: <arquivo dump + hash + horario>
- Cobertura: 3 VMs (app, db, suporte) confirmada: sim/nao

T039 - Restore rehearsal controlado:
- Ambiente: <central-homolog ou tenant-homolog>
- Inicio/fim:
- Resultado: sucesso/falha
- Validacoes: migrate:status, tenant:health, smoke minimo
- Evidencia anexada (log/comando):
```

Registro inicial desta fase (2026-05-06):

- Cobertura de backup das 3 VMs confirmada pelo responsavel da operacao.
- Pendencia: anexar nomes de arquivos dump/hash e horario de teste de restore controlado no fechamento do go-live.

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
curl -H "X-Internal-Token: ${INTERNAL_API_TOKEN}" "${APP_URL}/api/metrics" | rg "integration_(events|replays|outbox|deliveries|gateway)"
```

Valide fluxos manuais minimos:

- login no backoffice admin
- login de usuario ERP
- dashboard principal
- listagem de tenants/filiais
- criacao de um Vale de teste em homologacao ou tenant controlado
- consulta de estoque
- dashboard financeiro
- dashboard de backbone de integração em `/integration/backbone`
- painel central de billing em `/admin/billing`
- criação de plano e ativação de assinatura pelo painel central
- inspeção comercial em `/admin/billing/inspection`
- dashboard central de pagamentos em `/admin/payments`
- emissão manual controlada em `/admin/payments/emitir`
- inspeção financeira em `/admin/payments/inspection`
- dashboard central de recovery em `/admin/recovery`
- operação manual de recovery em `/admin/recovery/operacoes`
- inspeção de recovery em `/admin/recovery/inspection`
- dashboard central de analytics comercial em `/admin/analytics`
- inspeção analítica em `/admin/analytics/inspection`
- rebuild controlado do snapshot via `php artisan analytics:rebuild-platform-commercial-snapshot --days=30`
- filtro da API operacional `GET /api/integration/inspections?status=dead_letter`
- replay controlado de uma entrega com falha via `php artisan integration:replay <delivery_id> --operator=<user_id>`
- replay controlado de um retorno financeiro via `php artisan platform-payments:replay-return <return_id> --operator=<user_id>`
- logout

### Rollback comercial do módulo 011

Se o deploy introduzir inconsistência no control plane comercial:

- restaurar o dump do banco central anterior ao deploy
- invalidar eventos comerciais centrais pendentes em `evento_outboxes` se o restore for parcial
- rerodar validação mínima:
  - `php artisan test --compact tests/Feature/PlatformBillingSubscriptionLifecycleTest.php`
  - `php artisan test --compact tests/Feature/PlatformBillingBlockReactivationTest.php`
  - `php artisan test --compact tests/Feature/PlatformBillingBackbonePublicationTest.php`
- confirmar que `clientes.billing_blocked`, `assinaturas.status` e `faturas.status` retornaram ao último estado consistente

### Rollback operacional do módulo 012

Se o deploy introduzir inconsistência no ciclo de pagamentos SaaS:

- restaurar o dump do banco central anterior ao deploy
- revisar `cobrancas_saas_externas`, `retornos_pagamento_saas`, `conciliacoes_pagamento_saas` e `excecoes_conciliacao_saas` antes de reenfileirar qualquer replay
- diferenciar reversão financeira real de replay técnico:
  - replay técnico reprocessa o mesmo retorno já persistido
  - estorno, chargeback ou cancelamento exigem novo evento financeiro e não sobrescrevem a cobrança original
- rerodar validação mínima:
  - `php artisan test --compact tests/Feature/PlatformPaymentsWebhookSettlementTest.php`
  - `php artisan test --compact tests/Feature/PlatformPaymentsWebhookIdempotencyTest.php`
  - `php artisan test --compact tests/Feature/PlatformPaymentsReplayFlowTest.php`
- confirmar auditoria mínima:
  - `audit_logs.action=payment_return_replayed` quando houver replay manual
  - `retornos_pagamento_saas.processing_status` consistente com o último reprocessamento autorizado
  - `faturas.status` e `valor_pago` alinhados ao último retorno conciliado com segurança

### Rollback operacional do módulo 013

Se o deploy introduzir inconsistência na régua de recuperação de receita:

- restaurar o dump do banco central anterior ao deploy
- revisar `casos_recuperacao_receita`, `acoes_recuperacao_receita`, `compromissos_pagamento` e `indicadores_recuperacao_receita` antes de reenfileirar qualquer ação de cobrança
- diferenciar replay de comunicação de reversão operacional:
  - replay de comunicação reexecuta ação falha sem recriar caso novo
  - quebra de promessa, chargeback ou reabertura financeira exigem novo passo auditável, não sobrescrita silenciosa do histórico
- rerodar validação mínima:
  - `php artisan test --compact tests/Feature/PlatformRevenueRecoveryOpenCaseTest.php`
  - `php artisan test --compact tests/Feature/PlatformRevenueRecoveryPromiseTest.php`
  - `php artisan test --compact tests/Feature/PlatformRevenueRecoveryFiltersTest.php`
- confirmar consistência mínima:
  - `casos_recuperacao_receita.status` alinhado ao último estado comercial esperado
  - `acoes_recuperacao_receita` sem duplicidade indevida por estágio/canal
  - `compromissos_pagamento.status` e `suspends_until` coerentes com o último acordo válido

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
- métricas `integration_outbox_total`, `integration_deliveries_total` e `integration_replays_total` aparecem no scrape interno
- replay operacional gera registro em `audit_logs` para `action=replayed`

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

### Rollback Especifico: Tenant Resolution / Provisioning

Use este fluxo quando a falha afetar resolucao por subdominio, bloqueio de billing ou `tenant:create`:

1. Ativar manutencao (`php artisan down`) e congelar novos provisionamentos.
2. Reverter codigo para o commit/tag anterior estavel.
3. Executar somente migracoes de `central` necessarias ao rollback (ou restore completo se houve corrupcao/alteracao irreversivel).
4. Restaurar dump do `central` pre-go-live se houver divergencia em `clientes`, `assinaturas` ou `faturas`.
5. Para tenant impactado, restaurar dump especifico e validar conexao:
   - `php artisan tenant:health --json`
   - acesso por subdominio do tenant restaurado
6. Validar que:
   - tenant desconhecido retorna 404
   - tenant inativo/expirado retorna bloqueio esperado
   - tenant com billing vencido aplica negacao/redirect esperado
7. Reabrir aplicacao (`php artisan up`) apenas com smoke e healthcheck verdes.

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
