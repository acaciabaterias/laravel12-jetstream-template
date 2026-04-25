-- Demo data for the central catalog database.
-- All companies, contacts, addresses and identifiers below are fictional.

begin;

insert into public.planos
    (nome, slug, preco_mensal, max_usuarios, max_estoque_itens, has_white_label, has_support_priority, ativo, recursos, created_at, updated_at)
values
    (
        'Essencial',
        'essencial',
        299.90,
        5,
        1500,
        false,
        false,
        true,
        '{"modulos":["cadastros","estoque","vendas"],"filiais":1,"api":false}'::jsonb,
        now(),
        now()
    ),
    (
        'Profissional',
        'profissional',
        599.90,
        15,
        8000,
        true,
        true,
        true,
        '{"modulos":["cadastros","estoque","vendas","financeiro","garantias"],"filiais":3,"api":true}'::jsonb,
        now(),
        now()
    ),
    (
        'Enterprise Multi-Filial',
        'enterprise-multi-filial',
        1299.90,
        50,
        50000,
        true,
        true,
        true,
        '{"modulos":["cadastros","estoque","vendas","financeiro","garantias","logistica","open_finance"],"filiais":20,"api":true}'::jsonb,
        now(),
        now()
    )
on conflict (slug) do update
set
    nome = excluded.nome,
    preco_mensal = excluded.preco_mensal,
    max_usuarios = excluded.max_usuarios,
    max_estoque_itens = excluded.max_estoque_itens,
    has_white_label = excluded.has_white_label,
    has_support_priority = excluded.has_support_priority,
    ativo = excluded.ativo,
    recursos = excluded.recursos,
    updated_at = now();

insert into public.usuarios_plataforma
    (name, email, password, papel, ativo, ultimo_login, ultimo_ip, created_at, updated_at)
values
    (
        'Helena Prado',
        'helena.prado+superadmin@bateriaexpert.demo',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'super_admin',
        true,
        now() - interval '2 hours',
        '192.0.2.10',
        now(),
        now()
    ),
    (
        'Caio Menezes',
        'caio.menezes+support@bateriaexpert.demo',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'support',
        true,
        now() - interval '1 day',
        '192.0.2.11',
        now(),
        now()
    ),
    (
        'Livia Castro',
        'livia.castro+billing@bateriaexpert.demo',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'billing',
        true,
        now() - interval '3 days',
        '192.0.2.12',
        now(),
        now()
    )
on conflict (email) do update
set
    name = excluded.name,
    password = excluded.password,
    papel = excluded.papel,
    ativo = excluded.ativo,
    ultimo_login = excluded.ultimo_login,
    ultimo_ip = excluded.ultimo_ip,
    updated_at = now();

insert into public.clientes
    (
        cnpj,
        razao_social,
        nome_fantasia,
        email_contato,
        telefone,
        subdominio,
        status,
        trial_ends_at,
        subscription_ends_at,
        plano_atual_id,
        supabase_project_ref,
        supabase_url,
        supabase_db_host,
        supabase_db_password,
        supabase_anon_key,
        supabase_service_role_key,
        provisioning_status,
        billing_blocked,
        metadata,
        created_at,
        updated_at
    )
values
    (
        '12.345.678/0001-95',
        'Acumuladores Aurora Paulista Ltda.',
        'Aurora Baterias Paulista',
        'contato@aurorapaulista.demo',
        '(11) 3333-4100',
        'aurora-sp',
        'active',
        null,
        current_date + 60,
        (select id from public.planos where slug = 'profissional'),
        'auroraspdemo01',
        'https://auroraspdemo01.supabase.co',
        'db.auroraspdemo01.supabase.co',
        'SUPABASE_DEMO_PASSWORD_AURORA',
        'SUPABASE_DEMO_ANON_KEY_AURORA',
        'SUPABASE_DEMO_SERVICE_KEY_AURORA',
        'ready',
        false,
        '{
            "segmento":"centro automotivo",
            "cidade":"Sao Paulo",
            "uf":"SP",
            "endereco":"Avenida Brigadeiro Faria Lima, 1450, Jardim Paulistano",
            "responsavel":"Marina Tavares",
            "observacao":"Tenant demonstrativo com foco em operacao urbana."
        }'::jsonb,
        now(),
        now()
    ),
    (
        '23.456.789/0001-06',
        'Energia de Partida Mineira Comercio de Baterias Ltda.',
        'Mineira Power Center',
        'financeiro@mineirapower.demo',
        '(31) 3555-2080',
        'mineira-power',
        'active',
        null,
        current_date + 45,
        (select id from public.planos where slug = 'enterprise-multi-filial'),
        'mineirapowerd1',
        'https://mineirapowerd1.supabase.co',
        'db.mineirapowerd1.supabase.co',
        'SUPABASE_DEMO_PASSWORD_MINEIRA',
        'SUPABASE_DEMO_ANON_KEY_MINEIRA',
        'SUPABASE_DEMO_SERVICE_KEY_MINEIRA',
        'ready',
        false,
        '{
            "segmento":"distribuidor regional",
            "cidade":"Belo Horizonte",
            "uf":"MG",
            "endereco":"Rua dos Timbiras, 3200, Barro Preto",
            "responsavel":"Eduardo Falcao",
            "filiais":["Matriz BH","Contagem","Betim"]
        }'::jsonb,
        now(),
        now()
    ),
    (
        '34.567.890/0001-17',
        'Baterias Horizonte Sul Servicos Automotivos S.A.',
        'Horizonte Sul Baterias',
        'implantacao@horizontesul.demo',
        '(41) 3777-9900',
        'horizonte-sul',
        'trial',
        current_date + 12,
        null,
        (select id from public.planos where slug = 'essencial'),
        'horizontesuldm1',
        'https://horizontesuldm1.supabase.co',
        'db.horizontesuldm1.supabase.co',
        'SUPABASE_DEMO_PASSWORD_HORIZONTE',
        'SUPABASE_DEMO_ANON_KEY_HORIZONTE',
        'SUPABASE_DEMO_SERVICE_KEY_HORIZONTE',
        'provisioning',
        false,
        '{
            "segmento":"auto eletrica",
            "cidade":"Curitiba",
            "uf":"PR",
            "endereco":"Alameda Doutor Carlos de Carvalho, 980, Centro",
            "responsavel":"Patricia Amaral",
            "observacao":"Tenant de onboarding ainda em trial."
        }'::jsonb,
        now(),
        now()
    )
on conflict (subdominio) do update
set
    cnpj = excluded.cnpj,
    razao_social = excluded.razao_social,
    nome_fantasia = excluded.nome_fantasia,
    email_contato = excluded.email_contato,
    telefone = excluded.telefone,
    status = excluded.status,
    trial_ends_at = excluded.trial_ends_at,
    subscription_ends_at = excluded.subscription_ends_at,
    plano_atual_id = excluded.plano_atual_id,
    supabase_project_ref = excluded.supabase_project_ref,
    supabase_url = excluded.supabase_url,
    supabase_db_host = excluded.supabase_db_host,
    supabase_db_password = excluded.supabase_db_password,
    supabase_anon_key = excluded.supabase_anon_key,
    supabase_service_role_key = excluded.supabase_service_role_key,
    provisioning_status = excluded.provisioning_status,
    billing_blocked = excluded.billing_blocked,
    metadata = excluded.metadata,
    deleted_at = null,
    updated_at = now();

insert into public.white_label_configs
    (cliente_id, logo_url, favicon_url, cor_primaria, cor_secundaria, cor_fundo, titulo_login, custom_css, custom_js, template_nome, mostrar_marca_plataforma, created_at, updated_at)
values
    (
        (select id from public.clientes where subdominio = 'aurora-sp'),
        'https://assets.demo.local/aurora/logo.svg',
        'https://assets.demo.local/aurora/favicon.ico',
        '#0f4c81',
        '#f59e0b',
        '#f5f7fb',
        'Portal Aurora Baterias Paulista',
        '.demo-login-badge { letter-spacing: 0.08em; }',
        'window.demoTenant = "aurora-sp";',
        'corporate',
        false,
        now(),
        now()
    ),
    (
        (select id from public.clientes where subdominio = 'mineira-power'),
        'https://assets.demo.local/mineira/logo.svg',
        'https://assets.demo.local/mineira/favicon.ico',
        '#166534',
        '#f97316',
        '#f8fafc',
        'Painel Mineira Power Center',
        '.tenant-accent { border-radius: 12px; }',
        'window.demoTenant = "mineira-power";',
        'industrial',
        false,
        now(),
        now()
    )
on conflict (cliente_id) do update
set
    logo_url = excluded.logo_url,
    favicon_url = excluded.favicon_url,
    cor_primaria = excluded.cor_primaria,
    cor_secundaria = excluded.cor_secundaria,
    cor_fundo = excluded.cor_fundo,
    titulo_login = excluded.titulo_login,
    custom_css = excluded.custom_css,
    custom_js = excluded.custom_js,
    template_nome = excluded.template_nome,
    mostrar_marca_plataforma = excluded.mostrar_marca_plataforma,
    updated_at = now();

insert into public.assinaturas
    (cliente_id, plano_id, status, data_inicio, data_proximo_ciclo, data_termino, stripe_subscription_id, stripe_customer_id, observacoes, created_at, updated_at)
values
    (
        (select id from public.clientes where subdominio = 'aurora-sp'),
        (select id from public.planos where slug = 'profissional'),
        'active',
        current_date - 120,
        current_date + 30,
        null,
        'sub_demo_aurora_2026',
        'cus_demo_aurora_2026',
        'Contrato demonstrativo mensal com renovacao automatica.',
        now(),
        now()
    ),
    (
        (select id from public.clientes where subdominio = 'mineira-power'),
        (select id from public.planos where slug = 'enterprise-multi-filial'),
        'active',
        current_date - 210,
        current_date + 15,
        null,
        'sub_demo_mineira_2026',
        'cus_demo_mineira_2026',
        'Tenant demonstrativo enterprise com tres filiais habilitadas.',
        now(),
        now()
    ),
    (
        (select id from public.clientes where subdominio = 'horizonte-sul'),
        (select id from public.planos where slug = 'essencial'),
        'trial',
        current_date - 18,
        current_date + 12,
        null,
        null,
        'cus_demo_horizonte_trial',
        'Periodo de trial para avaliacao comercial.',
        now(),
        now()
    )
on conflict do nothing;

insert into public.faturas
    (assinatura_id, cliente_id, referencia, vencimento, valor, valor_pago, status, external_invoice_id, paid_at, payload_gateway, created_at, updated_at)
values
    (
        (select a.id from public.assinaturas a join public.clientes c on c.id = a.cliente_id where c.subdominio = 'aurora-sp' order by a.id desc limit 1),
        (select id from public.clientes where subdominio = 'aurora-sp'),
        '2026-04',
        current_date - 5,
        599.90,
        599.90,
        'paid',
        'inv_demo_aurora_2026_04',
        now() - interval '4 days',
        '{"gateway":"stripe","metodo":"cartao","bandeira":"visa"}'::jsonb,
        now(),
        now()
    ),
    (
        (select a.id from public.assinaturas a join public.clientes c on c.id = a.cliente_id where c.subdominio = 'mineira-power' order by a.id desc limit 1),
        (select id from public.clientes where subdominio = 'mineira-power'),
        '2026-04',
        current_date + 7,
        1299.90,
        null,
        'pending',
        'inv_demo_mineira_2026_04',
        null,
        '{"gateway":"stripe","metodo":"boleto"}'::jsonb,
        now(),
        now()
    ),
    (
        (select a.id from public.assinaturas a join public.clientes c on c.id = a.cliente_id where c.subdominio = 'horizonte-sul' order by a.id desc limit 1),
        (select id from public.clientes where subdominio = 'horizonte-sul'),
        '2026-05-trial',
        current_date + 12,
        0.00,
        null,
        'pending',
        'inv_demo_horizonte_trial',
        null,
        '{"gateway":"interno","metodo":"trial"}'::jsonb,
        now(),
        now()
    )
on conflict (assinatura_id, referencia) do update
set
    vencimento = excluded.vencimento,
    valor = excluded.valor,
    valor_pago = excluded.valor_pago,
    status = excluded.status,
    external_invoice_id = excluded.external_invoice_id,
    paid_at = excluded.paid_at,
    payload_gateway = excluded.payload_gateway,
    updated_at = now();

commit;
