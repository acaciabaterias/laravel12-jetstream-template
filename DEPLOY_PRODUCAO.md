# Deploy em Producao

## Checklist

1. Configurar `.env` do ERP Core.
2. Configurar `.env` de cada microservico.
3. Garantir acesso ao banco central.
4. Garantir Redis para filas.
5. Garantir workers assíncronos.
6. Rodar migrations canônicas.
7. Rodar seeders centrais.
8. Validar `healthcheck.sh`.

## Sequencia sugerida

### ERP Core

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --database=central --path=database/migrations/central --no-interaction
php artisan db:seed --class=PlanosSeeder --no-interaction
php artisan db:seed --class=SuperAdminSeeder --no-interaction
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Tenants

```bash
php artisan tenant:migrate-all --force
```

### Microservicos

Para cada microservico:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --no-interaction
```

## Operacao

- backup recorrente com `backup.sh`
- restore assistido com `restore.sh`
- monitoracao basica com `healthcheck.sh`
- workflows prontos em `.github/workflows`

## CI/CD

- `test.yml`: suite Laravel
- `lint.yml`: pint, php -l e validacao da collection
- `deploy.yml`: base para release manual

## Observacoes

- Este pacote foi preparado para adiantar o projeto sem depender de Docker.
- Quando o stack Docker estiver pronto, os mesmos artefatos continuam validos como base de referencia operacional.
