# Guia de Backup Completo - ERP BateriaExpert

## Objetivo

Este guia define o procedimento operacional para backup e restore do BateriaExpert ERP, cobrindo banco central, bancos tenant, arquivos de aplicacao, configuracoes sensiveis e validacoes de recuperacao.

## Escopo

O backup completo deve incluir:

- Banco central PostgreSQL, usado para catalogo SaaS, assinaturas, clientes e provisionamento.
- Bancos tenant PostgreSQL ou Supabase, isolados por cliente/CNPJ.
- Arquivos de configuracao de ambiente, como `.env` e secrets equivalentes.
- Artefatos de infraestrutura, como `docker-compose.yml`, manifests em `infra/` e configuracoes de monitoramento.
- Uploads e storage da aplicacao, quando habilitados em `storage/app`.
- Snapshots SQL canonicos em `database/schema/`.

## Politica Recomendada

- Backup central: diario, antes da janela de menor movimento.
- Backup tenant: diario para tenants ativos e antes de migrations tenant.
- Retencao curta: 7 backups diarios.
- Retencao media: 4 backups semanais.
- Retencao longa: 12 backups mensais.
- Teste de restore: pelo menos mensal ou antes de releases grandes.
- Criptografia: obrigatoria para dumps enviados a armazenamento externo.
- Acesso: restrito a operadores autorizados e auditado.

## Pre-requisitos

- `pg_dump` instalado no ambiente que executara o backup.
- `pg_restore` instalado para procedimentos de recuperacao.
- Credenciais PostgreSQL validas.
- Espaco livre suficiente no diretorio de destino.
- Variaveis de ambiente carregadas ou exportadas no shell.

## Backup do Banco Central

O repositorio ja possui o script [backup.sh](./backup.sh).

Exemplo minimo:

```bash
export BACKUP_DIR=./backups
export DB_CENTRAL_HOST=localhost
export DB_CENTRAL_PORT=5432
export DB_CENTRAL_DATABASE=erp_central
export DB_CENTRAL_USERNAME=gil
export DB_CENTRAL_PASSWORD=sua_senha

./backup.sh
```

O script gera um dump custom do PostgreSQL no formato:

```text
backups/central_erp_central_YYYYMMDD_HHMMSS.dump
```

## Backup de Um Tenant Pelo Script Shell

Para incluir um tenant especifico no mesmo ciclo:

```bash
export BACKUP_DIR=./backups
export DB_CENTRAL_HOST=localhost
export DB_CENTRAL_DATABASE=erp_central
export DB_CENTRAL_USERNAME=gil
export DB_CENTRAL_PASSWORD=sua_senha

export TENANT_DB_HOST=db.tenant.example
export TENANT_DB_PORT=5432
export TENANT_DB_NAME=tenant_empresa_001
export TENANT_DB_USER=postgres
export TENANT_DB_PASSWORD=senha_tenant

./backup.sh
```

O arquivo tenant sera criado como:

```text
backups/tenant_tenant_empresa_001_YYYYMMDD_HHMMSS.dump
```

## Backup de Tenants Pelo Artisan

O comando `tenant:backup` permite gerar backup por tenant usando ID, subdominio ou CNPJ.

Backup de um tenant:

```bash
php artisan tenant:backup norte --path=storage/app/backups
```

Backup de todos os tenants ativos:

```bash
php artisan tenant:backup --all --path=storage/app/backups
```

Modo simulacao, sem executar `pg_dump`:

```bash
php artisan tenant:backup norte --pretend
```

O scheduler do projeto ja agenda:

```text
tenant:backup --all diariamente as 03:00
```

## Backup de Arquivos da Aplicacao

Crie um pacote dos arquivos necessarios para reconstrucao operacional:

```bash
tar \
  --exclude=node_modules \
  --exclude=vendor \
  --exclude=storage/logs \
  --exclude=backups \
  -czf backups/app_files_$(date +%Y%m%d_%H%M%S).tar.gz \
  .env composer.json composer.lock package.json package-lock.json \
  docker-compose.yml docker-compose.monitoring.yml \
  app bootstrap config database resources routes infra docker
```

Se `storage/app` contiver anexos ou arquivos operacionais, gere backup separado:

```bash
tar -czf backups/storage_app_$(date +%Y%m%d_%H%M%S).tar.gz storage/app
```

## Restore do Banco Central

Use [restore.sh](./restore.sh):

```bash
export DB_CENTRAL_HOST=localhost
export DB_CENTRAL_PORT=5432
export DB_CENTRAL_USERNAME=gil
export DB_CENTRAL_PASSWORD=sua_senha

./restore.sh backups/central_erp_central_YYYYMMDD_HHMMSS.dump erp_central
```

O script executa `pg_restore` com:

- `--clean`
- `--if-exists`
- `--no-owner`
- `--no-privileges`

Use somente em janela controlada, porque objetos existentes podem ser removidos e recriados.

## Restore de Tenant

Para dumps custom gerados por `backup.sh`:

```bash
export DB_CENTRAL_HOST=db.tenant.example
export DB_CENTRAL_PORT=5432
export DB_CENTRAL_USERNAME=postgres
export DB_CENTRAL_PASSWORD=senha_tenant

./restore.sh backups/tenant_tenant_empresa_001_YYYYMMDD_HHMMSS.dump tenant_empresa_001
```

Para backups SQL gerados pelo comando `tenant:backup --format=sql`, use `psql` em ambiente controlado:

```bash
PGPASSWORD=senha_tenant psql \
  -h db.tenant.example \
  -p 5432 \
  -U postgres \
  -d tenant_empresa_001 \
  -f storage/app/backups/tenant_norte_YYYYMMDD_HHMMSS.sql
```

## Validacao Pos-Restore

Depois do restore:

```bash
php artisan migrate:status
php artisan tenant:list --status=active
php artisan tenant:health --json
./healthcheck.sh
php artisan test --compact
```

Tambem valide manualmente:

- Login de usuario admin.
- Login de usuario tenant.
- Dashboard principal.
- Criacao de Vale em ambiente de homologacao.
- Consulta de estoque.
- Painel financeiro.

## Checklist Operacional

- Backup gerado sem erro.
- Arquivo com tamanho coerente.
- Hash registrado.
- Dump enviado para armazenamento externo.
- Permissoes restritas aplicadas.
- Restore testado em ambiente isolado.
- Evidencia registrada no canal operacional.

## Comando de Hash

```bash
sha256sum backups/*.dump > backups/SHA256SUMS
```

## Incidente de Backup

Se o backup falhar:

1. Verifique credenciais e rede.
2. Verifique espaco em disco.
3. Rode `pg_dump --version`.
4. Execute novamente em modo manual.
5. Abra incidente se o backup diario nao for concluido ate o fim da janela operacional.
