-- Tenant RLS policies for Supabase / PostgreSQL 15+
-- Assumes database-per-client isolation already exists at the database level.
-- These policies focus on authenticated user access and role-aware write control.

begin;

create schema if not exists app;

create or replace function app.jwt_claims()
returns jsonb
language sql
stable
as $$
    select coalesce(
        nullif(current_setting('request.jwt.claims', true), ''),
        '{}'
    )::jsonb;
$$;

create or replace function app.current_app_user_id()
returns bigint
language sql
stable
as $$
    select nullif(app.jwt_claims()->>'app_user_id', '')::bigint;
$$;

create or replace function app.current_app_role()
returns text
language sql
stable
as $$
    select coalesce(app.jwt_claims()->>'papel', 'anonymous');
$$;

create or replace function app.is_active_user()
returns boolean
language sql
stable
as $$
    select exists (
        select 1
        from public.users u
        where u.id = app.current_app_user_id()
          and u.ativo = true
    );
$$;

create or replace function app.has_any_role(roles text[])
returns boolean
language sql
stable
as $$
    select app.current_app_role() = any(roles);
$$;

create or replace function app.can_write(roles text[])
returns boolean
language sql
stable
as $$
    select app.is_active_user() and app.has_any_role(roles);
$$;

do $$
declare
    tbl text;
    all_tables text[] := array[
        'users',
        'password_reset_tokens',
        'sessions',
        'permissoes',
        'papel_permissao',
        'audit_logs_acesso',
        'audit_logs',
        'clientes',
        'fornecedores',
        'fabricantes',
        'veiculos',
        'baterias',
        'aplicacoes',
        'depositos',
        'estoque_movimentacoes',
        'estoque_saldos',
        'xml_importacoes',
        'conta_sucata_movimentacoes',
        'vales',
        'itens_vale',
        'pedidos_venda',
        'ordens_servico',
        'reservas_estoque',
        'rotas_entrega',
        'pontos_entrega',
        'recebimentos_moveis',
        'geolocalizacao_eventos',
        'sync_eventos',
        'ordens_servico_garantia',
        'baterias_emprestimo',
        'notificacoes_whatsapp',
        'indices_retorno_produto',
        'contas_bancarias',
        'transacoes_financeiras',
        'fluxos_caixa_projetado',
        'margens_lucro_real',
        'conciliacoes_pendentes',
        'fechamentos_contabeis',
        'notas_fiscais_orquestradas',
        'boletos_orquestrados',
        'filas_contingencia',
        'cnab_remessas',
        'cnab_retorno_uploads'
    ];
begin
    foreach tbl in array all_tables loop
        execute format('alter table public.%I enable row level security', tbl);
        execute format('alter table public.%I force row level security', tbl);

        execute format('drop policy if exists service_role_all on public.%I', tbl);
        execute format(
            'create policy service_role_all on public.%I for all to service_role using (true) with check (true)',
            tbl
        );

        execute format('drop policy if exists authenticated_read on public.%I', tbl);
        execute format(
            'create policy authenticated_read on public.%I for select to authenticated using (app.is_active_user())',
            tbl
        );
    end loop;
end $$;

do $$
declare
    tbl text;
begin
    foreach tbl in array array[
        'users',
        'permissoes',
        'papel_permissao',
        'clientes',
        'fornecedores',
        'fabricantes',
        'veiculos',
        'baterias',
        'aplicacoes',
        'depositos',
        'contas_bancarias',
        'transacoes_financeiras',
        'fluxos_caixa_projetado',
        'margens_lucro_real',
        'conciliacoes_pendentes',
        'fechamentos_contabeis',
        'notas_fiscais_orquestradas',
        'boletos_orquestrados',
        'filas_contingencia',
        'cnab_remessas',
        'cnab_retorno_uploads'
    ] loop
        execute format('drop policy if exists authenticated_admin_write on public.%I', tbl);
        execute format(
            'create policy authenticated_admin_write on public.%I for all to authenticated using (app.can_write(array[''dono'',''gestor''])) with check (app.can_write(array[''dono'',''gestor'']))',
            tbl
        );
    end loop;
end $$;

do $$
declare
    tbl text;
begin
    foreach tbl in array array[
        'estoque_movimentacoes',
        'estoque_saldos',
        'xml_importacoes',
        'conta_sucata_movimentacoes',
        'reservas_estoque'
    ] loop
        execute format('drop policy if exists authenticated_stock_write on public.%I', tbl);
        execute format(
            'create policy authenticated_stock_write on public.%I for all to authenticated using (app.can_write(array[''dono'',''gestor'',''estoquista'',''tecnico''])) with check (app.can_write(array[''dono'',''gestor'',''estoquista'',''tecnico'']))',
            tbl
        );
    end loop;
end $$;

do $$
declare
    tbl text;
begin
    foreach tbl in array array[
        'vales',
        'itens_vale',
        'pedidos_venda'
    ] loop
        execute format('drop policy if exists authenticated_sales_write on public.%I', tbl);
        execute format(
            'create policy authenticated_sales_write on public.%I for all to authenticated using (app.can_write(array[''dono'',''gestor'',''vendedor''])) with check (app.can_write(array[''dono'',''gestor'',''vendedor'']))',
            tbl
        );
    end loop;
end $$;

do $$
declare
    tbl text;
begin
    foreach tbl in array array[
        'ordens_servico',
        'ordens_servico_garantia',
        'baterias_emprestimo',
        'notificacoes_whatsapp',
        'indices_retorno_produto'
    ] loop
        execute format('drop policy if exists authenticated_service_write on public.%I', tbl);
        execute format(
            'create policy authenticated_service_write on public.%I for all to authenticated using (app.can_write(array[''dono'',''gestor'',''tecnico''])) with check (app.can_write(array[''dono'',''gestor'',''tecnico'']))',
            tbl
        );
    end loop;
end $$;

do $$
declare
    tbl text;
begin
    foreach tbl in array array[
        'rotas_entrega',
        'pontos_entrega',
        'recebimentos_moveis',
        'geolocalizacao_eventos',
        'sync_eventos'
    ] loop
        execute format('drop policy if exists authenticated_logistics_write on public.%I', tbl);
        execute format(
            'create policy authenticated_logistics_write on public.%I for all to authenticated using (app.can_write(array[''dono'',''gestor'',''entregador'',''vendedor''])) with check (app.can_write(array[''dono'',''gestor'',''entregador'',''vendedor'']))',
            tbl
        );
    end loop;
end $$;

do $$
declare
    tbl text;
begin
    foreach tbl in array array[
        'audit_logs',
        'audit_logs_acesso'
    ] loop
        execute format('drop policy if exists authenticated_audit_insert on public.%I', tbl);
        execute format(
            'create policy authenticated_audit_insert on public.%I for insert to authenticated with check (app.is_active_user())',
            tbl
        );

        execute format('drop policy if exists authenticated_audit_admin_manage on public.%I', tbl);
        execute format(
            'create policy authenticated_audit_admin_manage on public.%I for all to authenticated using (app.can_write(array[''dono'',''gestor''])) with check (app.can_write(array[''dono'',''gestor'']))',
            tbl
        );
    end loop;
end $$;

commit;
