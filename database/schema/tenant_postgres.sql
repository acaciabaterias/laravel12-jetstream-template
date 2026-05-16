-- Tenant database snapshot
-- Source of truth consolidated from specs/001-009, current migrations and gap analysis.
-- Target: PostgreSQL 15+ / Supabase Postgres

begin;

create extension if not exists pgcrypto;

create table if not exists public.users (
    id bigint generated always as identity primary key,
    name varchar(150) not null,
    email varchar(150) not null unique,
    email_verified_at timestamptz,
    password text not null,
    papel varchar(30) not null default 'vendedor'
        check (papel in ('dono', 'gestor', 'vendedor', 'tecnico', 'estoquista', 'entregador')),
    ativo boolean not null default true,
    ultimo_login timestamptz,
    ultimo_ip inet,
    remember_token varchar(100),
    profile_photo_path text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists idx_users_papel on public.users(papel);
create index if not exists idx_users_ativo on public.users(ativo);

create table if not exists public.password_reset_tokens (
    email varchar(150) primary key,
    token text not null,
    created_at timestamptz
);

create table if not exists public.sessions (
    id varchar(255) primary key,
    user_id bigint references public.users(id) on delete set null,
    ip_address inet,
    user_agent text,
    payload text not null,
    last_activity integer not null
);

create index if not exists idx_sessions_user_id on public.sessions(user_id);
create index if not exists idx_sessions_last_activity on public.sessions(last_activity);

create table if not exists public.permissoes (
    id bigint generated always as identity primary key,
    nome varchar(100) not null,
    slug varchar(100) not null unique,
    descricao text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.papel_permissao (
    papel varchar(30) not null
        check (papel in ('dono', 'gestor', 'vendedor', 'tecnico', 'estoquista', 'entregador')),
    permissao_id bigint not null references public.permissoes(id) on delete cascade,
    created_at timestamptz not null default now(),
    primary key (papel, permissao_id)
);

create table if not exists public.audit_logs_acesso (
    id bigint generated always as identity primary key,
    user_id bigint references public.users(id) on delete cascade,
    ip inet not null,
    user_agent text,
    sucesso boolean not null,
    created_at timestamptz not null default now()
);

create table if not exists public.audit_logs (
    id bigint generated always as identity primary key,
    user_id bigint references public.users(id) on delete set null,
    action varchar(50) not null,
    table_name varchar(100) not null,
    record_id bigint not null,
    old_values jsonb,
    new_values jsonb,
    ip_address inet,
    user_agent text,
    created_at timestamptz not null default now()
);

create index if not exists idx_audit_logs_table_record on public.audit_logs(table_name, record_id);

create table if not exists public.clientes (
    id bigint generated always as identity primary key,
    nome varchar(150) not null,
    tipo_pessoa varchar(20) not null default 'fisica'
        check (tipo_pessoa in ('fisica', 'juridica')),
    documento varchar(30),
    email varchar(150),
    telefone varchar(30),
    celular varchar(30),
    cep varchar(12),
    endereco varchar(255),
    numero varchar(20),
    complemento varchar(120),
    bairro varchar(120),
    cidade varchar(120),
    uf varchar(2),
    observacoes text,
    ativo boolean not null default true,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create unique index if not exists idx_clientes_documento_unique
    on public.clientes(documento) where documento is not null;

create table if not exists public.fornecedores (
    id bigint generated always as identity primary key,
    nome varchar(150) not null,
    documento varchar(30),
    email varchar(150),
    telefone varchar(30),
    contato_nome varchar(120),
    cep varchar(12),
    endereco varchar(255),
    numero varchar(20),
    complemento varchar(120),
    bairro varchar(120),
    cidade varchar(120),
    uf varchar(2),
    observacoes text,
    ativo boolean not null default true,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create unique index if not exists idx_fornecedores_documento_unique
    on public.fornecedores(documento) where documento is not null;

create table if not exists public.fabricantes (
    id bigint generated always as identity primary key,
    nome varchar(150) not null,
    codigo varchar(60),
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    deleted_at timestamptz
);

create unique index if not exists idx_fabricantes_nome_unique
    on public.fabricantes(lower(nome))
    where deleted_at is null;

create table if not exists public.veiculos (
    id bigint generated always as identity primary key,
    fabricante_id bigint not null references public.fabricantes(id) on delete cascade,
    modelo varchar(150) not null,
    motorizacao varchar(60),
    ano_inicio integer,
    ano_fim integer,
    atributos_dinamicos jsonb,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    deleted_at timestamptz
);

create index if not exists idx_veiculos_fabricante_modelo on public.veiculos(fabricante_id, modelo);

create table if not exists public.baterias (
    id bigint generated always as identity primary key,
    sku varchar(80) not null unique,
    marca varchar(120) not null,
    tecnologia varchar(60),
    amperagem integer,
    polo varchar(20),
    preco_venda numeric(12,2) not null default 0,
    atributos_dinamicos jsonb,
    peso_sucata_kg numeric(10,2),
    valor_base_sucata_kg numeric(10,2),
    tem_logistica_reversa boolean not null default true,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    deleted_at timestamptz
);

create table if not exists public.aplicacoes (
    id bigint generated always as identity primary key,
    veiculo_id bigint not null references public.veiculos(id) on delete cascade,
    bateria_id bigint not null references public.baterias(id) on delete cascade,
    observacao text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    deleted_at timestamptz,
    unique (veiculo_id, bateria_id)
);

create table if not exists public.depositos (
    id bigint generated always as identity primary key,
    nome varchar(150) not null unique,
    tipo varchar(50) not null default 'principal',
    status varchar(30) not null default 'ativo',
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.estoque_movimentacoes (
    id bigint generated always as identity primary key,
    bateria_id bigint not null references public.baterias(id) on delete cascade,
    deposito_id bigint not null references public.depositos(id) on delete cascade,
    user_id bigint references public.users(id) on delete set null,
    tipo_operacao varchar(50) not null,
    origem varchar(80),
    quantidade integer not null check (quantidade >= 0),
    justificativa text,
    data_movimentacao timestamptz not null,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists idx_estoque_movimentacoes_deposito_bateria
    on public.estoque_movimentacoes(deposito_id, bateria_id);

create table if not exists public.estoque_saldos (
    id bigint generated always as identity primary key,
    bateria_id bigint not null references public.baterias(id) on delete cascade,
    deposito_id bigint not null references public.depositos(id) on delete cascade,
    quantidade_atual integer not null default 0 check (quantidade_atual >= 0),
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    unique (bateria_id, deposito_id)
);

create table if not exists public.xml_importacoes (
    id bigint generated always as identity primary key,
    chave_nfe varchar(80) not null unique,
    fornecedor_id bigint references public.fornecedores(id) on delete set null,
    status varchar(30) not null default 'pendente',
    log_erros text,
    payload_xml jsonb,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.conta_sucata_movimentacoes (
    id bigint generated always as identity primary key,
    entidade_tipo varchar(50) not null,
    entidade_id bigint,
    tipo_movimento varchar(30) not null,
    quantidade_kg numeric(10,2) not null,
    valor_unitario numeric(10,2) not null,
    saldo_resultante numeric(12,2) not null default 0,
    origem varchar(80),
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists idx_conta_sucata_entidade
    on public.conta_sucata_movimentacoes(entidade_tipo, entidade_id);

create table if not exists public.vales (
    id bigint generated always as identity primary key,
    cliente_id bigint not null references public.clientes(id) on delete cascade,
    vendedor_id bigint not null references public.users(id) on delete cascade,
    status varchar(30) not null default 'aberto',
    data_criacao timestamptz not null,
    data_faturamento timestamptz,
    observacoes text,
    created_by bigint not null references public.users(id) on delete cascade,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists idx_vales_cliente_status on public.vales(cliente_id, status);

create table if not exists public.itens_vale (
    id bigint generated always as identity primary key,
    vale_id bigint not null references public.vales(id) on delete cascade,
    bateria_id bigint not null references public.baterias(id) on delete cascade,
    quantidade integer not null check (quantidade > 0),
    preco_unitario_original numeric(12,2) not null,
    preco_unitario_final numeric(12,2) not null,
    flag_devolveu_sucata boolean not null default true,
    observacao text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.pedidos_venda (
    id bigint generated always as identity primary key,
    vale_id bigint not null references public.vales(id) on delete cascade,
    cliente_id bigint not null references public.clientes(id) on delete cascade,
    data_emissao timestamptz not null,
    valor_total numeric(12,2) not null,
    status varchar(30) not null default 'faturado',
    nf_referencia varchar(150),
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.ordens_servico (
    id bigint generated always as identity primary key,
    vale_id bigint not null references public.vales(id) on delete cascade,
    cliente_id bigint not null references public.clientes(id) on delete cascade,
    tecnico_responsavel_id bigint references public.users(id) on delete set null,
    data_abertura timestamptz not null,
    status varchar(30) not null default 'aberta',
    laudo text,
    observacoes text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.reservas_estoque (
    id bigint generated always as identity primary key,
    vale_id bigint not null references public.vales(id) on delete cascade,
    item_vale_id bigint not null references public.itens_vale(id) on delete cascade,
    bateria_id bigint not null references public.baterias(id) on delete cascade,
    deposito_id bigint not null references public.depositos(id) on delete cascade,
    quantidade integer not null check (quantidade > 0),
    status varchar(30) not null default 'reservada',
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.rotas_entrega (
    id bigint generated always as identity primary key,
    entregador_id bigint not null references public.users(id) on delete cascade,
    data_rota date not null,
    status varchar(30) not null default 'planejada',
    veiculo_id bigint references public.veiculos(id) on delete set null,
    observacoes text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.pontos_entrega (
    id bigint generated always as identity primary key,
    rota_entrega_id bigint not null references public.rotas_entrega(id) on delete cascade,
    vale_id bigint references public.vales(id) on delete set null,
    cliente_id bigint not null references public.clientes(id) on delete cascade,
    endereco_entrega varchar(255) not null,
    ordem_parada integer not null,
    status varchar(30) not null default 'planejado',
    peso_sucata_coletado numeric(10,2),
    observacao text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.recebimentos_moveis (
    id bigint generated always as identity primary key,
    ponto_entrega_id bigint not null references public.pontos_entrega(id) on delete cascade,
    valor numeric(12,2) not null,
    metodo_pagamento varchar(40) not null,
    status_sincronizado boolean not null default false,
    comprovante_path text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.geolocalizacao_eventos (
    id bigint generated always as identity primary key,
    rota_entrega_id bigint references public.rotas_entrega(id) on delete set null,
    ponto_entrega_id bigint references public.pontos_entrega(id) on delete set null,
    latitude numeric(10,7) not null,
    longitude numeric(10,7) not null,
    tipo_evento varchar(50) not null,
    recorded_at timestamptz not null
);

create table if not exists public.sync_eventos (
    id bigint generated always as identity primary key,
    dispositivo_uuid uuid not null,
    entidade_tipo varchar(50) not null,
    entidade_id bigint,
    payload_hash varchar(120) not null unique,
    payload jsonb not null,
    status varchar(30) not null default 'pendente',
    processed_at timestamptz,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.ordens_servico_garantia (
    id bigint generated always as identity primary key,
    cliente_id bigint not null references public.clientes(id) on delete cascade,
    bateria_id bigint not null references public.baterias(id) on delete cascade,
    vale_original_id bigint references public.vales(id) on delete set null,
    data_abertura timestamptz not null,
    status varchar(30) not null default 'aberta',
    laudo text,
    resultado varchar(30),
    cobranca_valor numeric(12,2),
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.baterias_emprestimo (
    id bigint generated always as identity primary key,
    os_garantia_id bigint not null references public.ordens_servico_garantia(id) on delete cascade,
    bateria_usada_id bigint not null references public.baterias(id) on delete cascade,
    data_retirada timestamptz not null,
    data_devolucao_prevista timestamptz not null,
    data_devolucao_real timestamptz,
    termo_arquivo_path text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.notificacoes_whatsapp (
    id bigint generated always as identity primary key,
    os_garantia_id bigint not null references public.ordens_servico_garantia(id) on delete cascade,
    cliente_telefone varchar(30),
    status varchar(30) not null default 'pendente',
    mensagem text not null,
    data_envio timestamptz,
    identificador_externo varchar(150),
    tracking_error text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.indices_retorno_produto (
    id bigint generated always as identity primary key,
    bateria_id bigint not null references public.baterias(id) on delete cascade,
    periodo_inicio date not null,
    periodo_fim date not null,
    total_vendidas integer not null default 0,
    total_garantias integer not null default 0,
    indice_calculado numeric(8,4) not null default 0,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.contas_bancarias (
    id bigint generated always as identity primary key,
    banco varchar(120) not null,
    agencia varchar(30) not null,
    conta varchar(40) not null,
    tipo varchar(30) not null,
    token_api text,
    status varchar(30) not null default 'ativa',
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.transacoes_financeiras (
    id bigint generated always as identity primary key,
    conta_bancaria_id bigint not null references public.contas_bancarias(id) on delete cascade,
    tipo varchar(30) not null,
    valor numeric(12,2) not null,
    data_transacao timestamptz not null,
    status_conciliado boolean not null default false,
    origem_tipo varchar(50),
    origem_id bigint,
    descricao varchar(255),
    identificador_externo varchar(150) not null unique,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists idx_transacoes_financeiras_origem
    on public.transacoes_financeiras(origem_tipo, origem_id);

create table if not exists public.fluxos_caixa_projetado (
    id bigint generated always as identity primary key,
    data_referencia date not null,
    saldo_inicial numeric(12,2) not null default 0,
    total_receber numeric(12,2) not null default 0,
    total_pagar numeric(12,2) not null default 0,
    saldo_projetado numeric(12,2) not null default 0,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.margens_lucro_real (
    id bigint generated always as identity primary key,
    bateria_id bigint not null references public.baterias(id) on delete cascade,
    periodo_inicio date not null,
    periodo_fim date not null,
    valor_venda numeric(12,2) not null default 0,
    custo_aquisicao numeric(12,2) not null default 0,
    frete numeric(12,2) not null default 0,
    imposto numeric(12,2) not null default 0,
    comissao numeric(12,2) not null default 0,
    margem_calculada numeric(8,4) not null default 0,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.conciliacoes_pendentes (
    id bigint generated always as identity primary key,
    transacao_financeira_id bigint not null references public.transacoes_financeiras(id) on delete cascade,
    motivo varchar(255) not null,
    payload_bancario jsonb,
    status varchar(30) not null default 'pendente',
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.fechamentos_contabeis (
    id bigint generated always as identity primary key,
    competencia varchar(20) not null,
    status varchar(30) not null default 'aberto',
    fechado_em timestamptz,
    fechado_por bigint references public.users(id) on delete set null,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now(),
    unique (competencia)
);

create table if not exists public.notas_fiscais_orquestradas (
    id bigint generated always as identity primary key,
    vale_id bigint not null references public.vales(id) on delete cascade,
    chave_acesso varchar(80),
    xml_path text,
    status varchar(30) not null default 'pendente',
    ms_requisicao_id varchar(150),
    idempotency_key varchar(150) not null unique,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.boletos_orquestrados (
    id bigint generated always as identity primary key,
    vale_id bigint not null references public.vales(id) on delete cascade,
    nosso_numero varchar(80),
    linha_digitavel varchar(255),
    pdf_url text,
    status varchar(30) not null default 'pendente',
    identificador_externo varchar(150),
    idempotency_key varchar(150) not null unique,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.filas_contingencia (
    id bigint generated always as identity primary key,
    tipo_integracao varchar(50) not null,
    payload jsonb not null,
    tentativas integer not null default 0,
    proxima_tentativa timestamptz,
    status varchar(30) not null default 'pendente',
    ultimo_erro text,
    idempotency_key varchar(150) not null unique,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.cnab_remessas (
    id bigint generated always as identity primary key,
    tipo_arquivo varchar(30) not null,
    nome_arquivo varchar(255) not null,
    status varchar(30) not null default 'gerada',
    arquivo_path text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table if not exists public.cnab_retorno_uploads (
    id bigint generated always as identity primary key,
    cnab_remessa_id bigint references public.cnab_remessas(id) on delete set null,
    nome_arquivo varchar(255) not null,
    status_processamento varchar(30) not null default 'pendente',
    log_processamento text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

commit;
