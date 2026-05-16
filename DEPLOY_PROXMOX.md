# Deploy no Proxmox

Este guia assume um host Proxmox com VMs ou LXC separados para:

- ERP Core
- PostgreSQL central
- Redis
- Microservicos
- Reverse proxy

## Topologia sugerida

- `vm-app-01`: ERP Core Laravel
- `vm-db-01`: PostgreSQL central local
- `vm-ms-01`: microservicos `MS-001` a `MS-005`
- `vm-edge-01`: Nginx / Caddy / Traefik

## Provisionamento sugerido

1. Criar uma VM base Ubuntu 24.04.
2. Instalar `git`, `php`, `composer`, `nginx`, `supervisor`, `redis` e `postgresql-client`.
3. Clonar o monorepo.
4. Configurar `.env` do ERP Core com `DB_CONNECTION=central` e `DB_CENTRAL_*`.
5. Configurar `.env` de cada microservico.
6. Apontar o proxy reverso para:
   - ERP: `:8000`
   - MS-001: `:8001`
   - MS-002: `:8002`
   - MS-003: `:8003`
   - MS-004: `:8004`
   - MS-005: `:8005`

## Processo de release

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --database=central --path=database/migrations/central --no-interaction
php artisan queue:restart
```

## Microservicos

Cada microservico pode rodar como processo PHP separado via:

- `php artisan serve --host=0.0.0.0 --port=800X`
- `supervisord`
- `systemd`

Exemplo de unidade `systemd`:

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

## Backup

Use:

```bash
./backup.sh
```

## Healthcheck

Use:

```bash
./healthcheck.sh
```
