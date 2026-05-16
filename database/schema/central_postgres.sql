-- Central database snapshot
-- Source of truth consolidated from specs/001-009 and current Laravel migrations.
-- Target: PostgreSQL 15+ / Supabase Postgres

begin;

create extension if not exists pgcrypto;

create table if not exists public.planos (
    id bigint generated always as identity primary key,
    nome varchar(80) not null,
    slug varchar(80) not null unique,
    preco_mensal numeric(12,2) not null default 0,
    max_usuarios integer not null default 3,
    max_estoque_itens integer not null default 500,
    has_white_label boolean not null default false,
    has_support_priority boolean not null default false,
    ativo boolean not null default true,
    recursos jsonb not null default '{}'::jsonb,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.clientes (
    id bigint generated always as identity primary key,
    cnpj varchar(18) not null unique,
    razao_social varchar(150) not null,
    nome_fantasia varchar(100),
    email_contato varchar(150) not null,
    telefone varchar(30),
    subdominio varchar(80) not null unique,
    status varchar(30) not null default 'trial'
        check (status in ('trial', 'active', 'expired', 'cancelled', 'suspended', 'provisioning')),
    trial_ends_at date,
    subscription_ends_at date,
    plano_atual_id bigint references public.planos(id) on delete set null,
    supabase_project_ref varchar(100) unique,
    supabase_url text,
    supabase_db_host text,
    supabase_db_password text,
    supabase_anon_key text,
    supabase_service_role_key text,
    provisioning_status varchar(30) not null default 'pending'
        check (provisioning_status in ('pending', 'provisioning', 'ready', 'failed')),
    billing_blocked boolean not null default false,
    metadata jsonb not null default '{}'::jsonb,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    deleted_at timestamptz
);

create index if not exists idx_clientes_status on public.clientes(status);
create index if not exists idx_clientes_plano_atual_id on public.clientes(plano_atual_id);

create table if not exists public.white_label_configs (
    id bigint generated always as identity primary key,
    cliente_id bigint not null unique references public.clientes(id) on delete cascade,
    logo_url text,
    favicon_url text,
    cor_primaria varchar(7) not null default '#3b82f6',
    cor_secundaria varchar(7) not null default '#10b981',
    cor_fundo varchar(7) not null default '#f9fafb',
    titulo_login varchar(150),
    custom_css text,
    custom_js text,
    template_nome varchar(80) not null default 'default',
    mostrar_marca_plataforma boolean not null default true,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.usuarios_plataforma (
    id bigint generated always as identity primary key,
    name varchar(100) not null,
    email varchar(150) not null unique,
    password text not null,
    papel varchar(30) not null default 'support'
        check (papel in ('super_admin', 'support', 'billing')),
    ativo boolean not null default true,
    ultimo_login timestamptz,
    ultimo_ip inet,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists idx_usuarios_plataforma_papel on public.usuarios_plataforma(papel);

create table if not exists public.assinaturas (
    id bigint generated always as identity primary key,
    cliente_id bigint not null references public.clientes(id) on delete cascade,
    plano_id bigint not null references public.planos(id) on delete restrict,
    status varchar(30) not null
        check (status in ('trial', 'active', 'expired', 'cancelled', 'past_due', 'paused')),
    data_inicio date not null,
    data_proximo_ciclo date not null,
    data_termino date,
    stripe_subscription_id varchar(150),
    stripe_customer_id varchar(150),
    observacoes text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists idx_assinaturas_cliente_id on public.assinaturas(cliente_id);
create index if not exists idx_assinaturas_status on public.assinaturas(status);

create table if not exists public.faturas (
    id bigint generated always as identity primary key,
    assinatura_id bigint not null references public.assinaturas(id) on delete cascade,
    cliente_id bigint not null references public.clientes(id) on delete cascade,
    referencia varchar(30) not null,
    vencimento date not null,
    valor numeric(12,2) not null,
    valor_pago numeric(12,2),
    status varchar(30) not null default 'pending'
        check (status in ('pending', 'paid', 'overdue', 'cancelled', 'refunded')),
    external_invoice_id varchar(150),
    paid_at timestamptz,
    payload_gateway jsonb not null default '{}'::jsonb,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    unique (assinatura_id, referencia)
);

create index if not exists idx_faturas_cliente_id on public.faturas(cliente_id);
create index if not exists idx_faturas_status on public.faturas(status);
create index if not exists idx_faturas_vencimento on public.faturas(vencimento);

commit;
