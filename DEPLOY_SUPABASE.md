# Deploy com Supabase

Este guia foca no modelo `database-per-client`.

## Banco central

Você pode manter o banco central:

- local PostgreSQL
- Supabase dedicado para metadados SaaS

O projeto hoje assume a conexao `central` configurada por:

```dotenv
DB_CONNECTION=central
DB_CENTRAL_DRIVER=pgsql
DB_CENTRAL_HOST=...
DB_CENTRAL_PORT=5432
DB_CENTRAL_DATABASE=erp_central
DB_CENTRAL_USERNAME=...
DB_CENTRAL_PASSWORD=...
```

## Bancos tenant

Cada tenant/CNPJ deve possuir:

- banco PostgreSQL isolado
- schema `public`
- migrations em `database/migrations/tenant`
- policies em `database/schema/tenant_rls_policies.sql`

## Passos sugeridos

1. Provisionar o projeto Supabase do tenant.
2. Registrar os metadados no banco central.
3. Configurar o `TenantConnectionMiddleware`.
4. Rodar:

```bash
php artisan migrate --database=tenant --path=database/migrations/tenant --no-interaction
```

5. Aplicar seeders do tenant conforme a necessidade.

## RLS

As policies canônicas já estão em:

- [database/schema/tenant_rls_policies.sql](/home/gil/laravel12-jetstream-template/database/schema/tenant_rls_policies.sql)

E a migration correspondente está em:

- [database/migrations/tenant/2026_04_23_000006_apply_tenant_rls_policies.php](/home/gil/laravel12-jetstream-template/database/migrations/tenant/2026_04_23_000006_apply_tenant_rls_policies.php)

## Verificacao

Antes do primeiro deploy do central:

```bash
./check-pg.sh
```
