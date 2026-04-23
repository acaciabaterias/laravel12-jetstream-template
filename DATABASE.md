# BateriaExpert Database Guide

## Estratégia de Banco

O projeto usa dois contextos de banco:

- `central`: catálogo SaaS, provisionamento, planos, assinaturas e usuários de plataforma
- `tenant`: banco operacional isolado por CNPJ/tenant

## Banco Central

Principais entidades:

- `clientes`
- `planos`
- `assinaturas`
- `faturas`
- `white_label_configs`
- `usuarios_plataforma`

Objetivos:

- registrar clientes e subdomínios
- controlar provisionamento de tenants
- armazenar credenciais e metadados operacionais
- suportar billing, trial, expiração e suporte administrativo

## Banco do Tenant

O banco do tenant concentra todas as tabelas do ERP operacional:

- acesso e RBAC
- cadastros estruturais
- estoque e depósitos
- vendas, vales, pedidos e OS
- logística e entregas
- garantias
- financeiro
- orquestração fiscal e bancária
- auditoria

## Convenções

- isolamento por banco físico, não por coluna
- chaves estrangeiras internas ao tenant
- auditoria em `audit_logs`
- tabelas financeiras e orquestradoras mantêm vínculo com entidades do tenant
- snapshots canônicos ficam em `database/schema/`

## Migrations

- `database/migrations/central`: migrations do catálogo central
- `database/migrations/tenant`: migrations canônicas dos bancos operacionais
- `database/migrations/`: migrations legadas e complementares do projeto atual

## Provisionamento

O comando `tenant:create` localiza o cliente no catálogo central, provisiona o banco físico e executa as migrations do tenant quando a conexão PostgreSQL está disponível.

## Operação e Manutenção

Comandos úteis:

- `tenant:list`
- `tenant:health`
- `tenant:backup`
- `tenant:migrate-all`
- `tenant:export`

## Boas Práticas

- nunca usar `filial_id` como mecanismo de tenancy
- sempre validar mudanças de schema contra `central_postgres.sql` e `tenant_postgres.sql`
- manter factories, seeders e testes alinhados ao schema canônico
