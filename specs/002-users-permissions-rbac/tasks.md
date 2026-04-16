# Tasks: Módulo de Usuários e Perfis (RBAC)

**Feature Branch**: `002-users-permissions-rbac`
**Spec File**: [spec.md](spec.md)

## Phase 1: Database
- [ ] T001: Atualizar migration da tabela de `users` com `role_id` ou campo `role` ENUM.
- [ ] T002: Criar migration para tabela pivô `filial_user` para ligações N:N entre usuário e as filiais onde atua.
- [ ] T003: Criar migration `login_audits` (user_id, ip_address, user_agent).

## Phase 2: Logic
- [ ] T004: Atualizar Controller de Autenticação/Jetstream Fortify para gravar na tabela `login_audits`.
- [ ] T005: Registrar Gates no `AuthServiceProvider` do Laravel baseados em cada um dos seis papéis definidos.
- [ ] T006: Criar Componente Livewire 4 (CRUD) para gerenciar Usuários e relacioná-los com filiais (Acesso restrito ao "Dono").

## Phase 3: Testing
- [ ] T007: Testes automatizados para login reportando auditoria de IP corretamente.
- [ ] T008: Testes de Autorização (Gates) permitindo e rejeitando acessos aos endpoints conformemente.
