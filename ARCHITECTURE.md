# BateriaExpert Architecture

## Visão Geral

O ERP BateriaExpert segue uma arquitetura Laravel monolítica para o core do ERP, com microserviços especializados para domínios externos como fiscal, bancário, notificações, Open Finance e geocoding.

## Componentes Principais

- `app/`: regras de negócio, Livewire, policies, jobs, eventos e integrações do ERP
- `database/migrations/central`: catálogo SaaS central, tenants, planos e billing
- `database/migrations/tenant`: schema canônico de cada banco operacional do ERP
- `microservicos/`: APIs desacopladas para integrações especializadas

## Multi-Tenancy

- O banco central mantém catálogo de clientes, credenciais e billing
- Cada tenant opera em banco físico isolado
- A resolução da conexão ativa acontece via `TenantConnectionMiddleware`
- O core do ERP não usa `filial_id` como mecanismo de isolamento

## Fluxo de Aplicação

1. O usuário acessa o domínio do tenant
2. O middleware resolve o tenant no catálogo central
3. A aplicação troca a conexão para o banco físico do tenant
4. O módulo solicitado executa regras, jobs e eventos localmente
5. Quando necessário, o core delega para microserviços externos

## Módulos Core

- `001`: tenant management e catálogo central
- `002`: autenticação e RBAC
- `003`: cadastros estruturais
- `004`: estoque e logística reversa
- `005`: vendas, vales e OS
- `006`: logística e entregas
- `007`: garantias e feedback
- `008`: financeiro inteligente
- `009`: orquestração fiscal e bancária

## Integrações Externas

- `MS-001`: fiscal ACBr
- `MS-002`: bancário/CNAB
- `MS-003`: WhatsApp e workflows
- `MS-004`: Open Finance
- `MS-005`: geocoding e rotas

## Padrões Técnicos

- Laravel 12 como núcleo de aplicação
- Livewire para interfaces reativas
- Jobs enfileirados para fluxos assíncronos
- Events/Listeners para desacoplamento de domínio
- Policies e Gates para controle de acesso
- PostgreSQL/Supabase como referência de persistência
