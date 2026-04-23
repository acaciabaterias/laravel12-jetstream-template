# PostgreSQL Local Setup

Este guia prepara o PostgreSQL local no Linux para a conexão `central` usada pelo projeto.

## Alvo

- Host: `localhost`
- Porta: `5432`
- Banco: `erp_central`
- Usuário padrão do projeto: `gil`

Os mesmos valores podem ser sobrescritos por `DB_CENTRAL_*` no `.env`.

## 1. Instalar PostgreSQL

Ubuntu / Debian:

```bash
sudo apt update
sudo apt install -y postgresql postgresql-contrib
```

Fedora:

```bash
sudo dnf install -y postgresql-server postgresql-contrib
sudo postgresql-setup --initdb
```

Arch:

```bash
sudo pacman -S postgresql
sudo -iu postgres initdb --locale=C.UTF-8 -D /var/lib/postgres/data
```

## 2. Subir o serviço

Distribuições com `systemd`:

```bash
sudo systemctl enable --now postgresql
sudo systemctl status postgresql
```

Se sua distro expõe um serviço versionado, use algo como:

```bash
sudo systemctl enable --now postgresql-16
```

## 3. Criar usuário e banco

Entre no shell do usuário `postgres`:

```bash
sudo -iu postgres psql
```

Dentro do `psql`, rode:

```sql
create role gil with login password 'sua_senha_forte_aqui';
alter role gil createdb;

create database erp_central owner gil;

grant all privileges on database erp_central to gil;
\q
```

Se o usuário já existir:

```sql
alter role gil with login password 'sua_senha_forte_aqui';
alter database erp_central owner to gil;
grant all privileges on database erp_central to gil;
```

## 4. Garantir acesso local por senha

Abra o `pg_hba.conf`. Em Debian/Ubuntu normalmente fica em:

```bash
sudo nano /etc/postgresql/*/main/pg_hba.conf
```

Em Fedora/Arch normalmente:

```bash
sudo nano /var/lib/pgsql/data/pg_hba.conf
sudo nano /var/lib/postgres/data/pg_hba.conf
```

Garanta uma linha local parecida com:

```conf
host    all             all             127.0.0.1/32            scram-sha-256
host    all             all             ::1/128                 scram-sha-256
```

Depois reinicie:

```bash
sudo systemctl restart postgresql
```

## 5. Configurar o `.env`

Use algo assim:

```dotenv
DB_CONNECTION=central

DB_CENTRAL_DRIVER=pgsql
DB_CENTRAL_HOST=localhost
DB_CENTRAL_PORT=5432
DB_CENTRAL_DATABASE=erp_central
DB_CENTRAL_USERNAME=gil
DB_CENTRAL_PASSWORD=sua_senha_forte_aqui
```

## 6. Validar a conexão

Teste pelo `psql`:

```bash
PGPASSWORD='sua_senha_forte_aqui' psql -h localhost -p 5432 -U gil -d erp_central -c 'select current_database(), current_user;'
```

Teste pelo projeto:

```bash
php artisan migrate --database=central --path=database/migrations/central --no-interaction
```

## 7. Script de diagnóstico

O projeto agora inclui:

```bash
bash ./check-pg.sh
```

Você pode sobrescrever os defaults:

```bash
DB_CENTRAL_HOST=localhost \
DB_CENTRAL_PORT=5432 \
DB_CENTRAL_DATABASE=erp_central \
DB_CENTRAL_USERNAME=gil \
DB_CENTRAL_PASSWORD='sua_senha' \
bash ./check-pg.sh
```

## Erros comuns

`connection refused`

- O serviço não está rodando.
- A porta está diferente de `5432`.
- O PostgreSQL está ouvindo só em socket local.

`password authentication failed`

- Usuário ou senha estão errados.
- O `pg_hba.conf` está em `peer` e não em `scram-sha-256`/`md5` para acesso TCP local.

`database "erp_central" does not exist`

- O banco ainda não foi criado.

`permission denied for schema public`

- O usuário conecta, mas não tem privilégio para criar tabelas no schema `public`.
- Corrija no `psql`:

```sql
grant usage, create on schema public to gil;
alter schema public owner to gil;
```
