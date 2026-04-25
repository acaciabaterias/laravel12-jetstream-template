-- Demo data for tenant financial and orchestrator flows.
-- All payment references, bank details and fiscal identifiers below are fictional.

begin;

insert into public.contas_bancarias
    (banco, agencia, conta, tipo, token_api, status, created_at, updated_at)
values
    (
        'Banco Cooperativo Paulista',
        '1234',
        '102938-5',
        'corrente',
        'TOKEN_DEMO_BANCO_PAULISTA_001',
        'ativa',
        now(),
        now()
    ),
    (
        'Banco Mercantil do Sudeste',
        '4321',
        '554433-0',
        'corrente',
        'TOKEN_DEMO_BANCO_SUDESTE_002',
        'ativa',
        now(),
        now()
    )
on conflict do nothing;

insert into public.transacoes_financeiras
    (conta_bancaria_id, tipo, valor, data_transacao, status_conciliado, origem_tipo, origem_id, descricao, identificador_externo, created_at, updated_at)
values
    (
        (select id from public.contas_bancarias where conta = '102938-5' limit 1),
        'receita',
        1119.80,
        now() - interval '3 days',
        true,
        'pedido_venda',
        (select id from public.pedidos_venda where nf_referencia = 'NF-2026-000184' limit 1),
        'Recebimento via boleto da Transportadora Serra Azul.',
        'fin-demo-receb-0001',
        now(),
        now()
    ),
    (
        (select id from public.contas_bancarias where conta = '102938-5' limit 1),
        'despesa',
        2850.00,
        now() - interval '2 days',
        false,
        'fornecedor',
        null,
        'Pagamento agendado de lote de baterias AGM para fornecedor homologado.',
        'fin-demo-desp-0001',
        now(),
        now()
    ),
    (
        (select id from public.contas_bancarias where conta = '554433-0' limit 1),
        'receita',
        389.90,
        now() - interval '8 hours',
        false,
        'vale',
        (select id from public.vales where observacoes = 'Reserva de item para cliente final aguardando instalacao.' limit 1),
        'Sinal recebido por PIX para reserva de bateria Bosch 45Ah.',
        'fin-demo-pix-0001',
        now(),
        now()
    )
on conflict (identificador_externo) do update
set
    conta_bancaria_id = excluded.conta_bancaria_id,
    tipo = excluded.tipo,
    valor = excluded.valor,
    data_transacao = excluded.data_transacao,
    status_conciliado = excluded.status_conciliado,
    origem_tipo = excluded.origem_tipo,
    origem_id = excluded.origem_id,
    descricao = excluded.descricao,
    updated_at = now();

insert into public.conciliacoes_pendentes
    (transacao_financeira_id, motivo, payload_bancario, status, created_at, updated_at)
values
    (
        (select id from public.transacoes_financeiras where identificador_externo = 'fin-demo-desp-0001'),
        'Pagamento identificado no extrato sem comprovante fiscal vinculado.',
        '{"banco":"Banco Cooperativo Paulista","historico":"TED lote fornecedor 8492","valor":2850.00}'::jsonb,
        'pendente',
        now(),
        now()
    ),
    (
        (select id from public.transacoes_financeiras where identificador_externo = 'fin-demo-pix-0001'),
        'Entrada PIX aguardando baixa manual no vale em aberto.',
        '{"canal":"pix","e2e_id":"E2EDEMO000001","valor":389.90}'::jsonb,
        'em_analise',
        now(),
        now()
    )
on conflict do nothing;

insert into public.fluxos_caixa_projetado
    (data_referencia, saldo_inicial, total_receber, total_pagar, saldo_projetado, created_at, updated_at)
values
    (current_date, 18450.00, 3509.70, 2850.00, 19109.70, now(), now()),
    (current_date + 1, 19109.70, 840.00, 620.00, 19329.70, now(), now()),
    (current_date + 2, 19329.70, 1120.00, 940.00, 19509.70, now(), now())
on conflict do nothing;

insert into public.margens_lucro_real
    (bateria_id, periodo_inicio, periodo_fim, valor_venda, custo_aquisicao, frete, imposto, comissao, margem_calculada, created_at, updated_at)
values
    (
        (select id from public.baterias where sku = 'AUR-060-AGM-D'),
        current_date - 30,
        current_date,
        1119.80,
        760.00,
        48.00,
        134.38,
        55.99,
        0.1084,
        now(),
        now()
    ),
    (
        (select id from public.baterias where sku = 'AUR-045-CONV-D'),
        current_date - 30,
        current_date,
        389.90,
        245.00,
        16.00,
        46.79,
        19.49,
        0.1606,
        now(),
        now()
    )
on conflict do nothing;

insert into public.fechamentos_contabeis
    (competencia, status, fechado_em, fechado_por, created_at, updated_at)
values
    (
        '2026-03',
        'fechado',
        now() - interval '12 days',
        (select id from public.users where email = 'larissa.campos+gestor@aurorapaulista.demo'),
        now(),
        now()
    ),
    (
        '2026-04',
        'aberto',
        null,
        null,
        now(),
        now()
    )
on conflict (competencia) do update
set
    status = excluded.status,
    fechado_em = excluded.fechado_em,
    fechado_por = excluded.fechado_por,
    updated_at = now();

insert into public.notas_fiscais_orquestradas
    (vale_id, chave_acesso, xml_path, status, ms_requisicao_id, idempotency_key, created_at, updated_at)
values
    (
        (select id from public.vales where observacoes = 'Troca de duas baterias da frota de entregas com recolhimento de sucata.' limit 1),
        '35260412345678000195550010000001841000001841',
        's3://demo-fiscal/notas/2026/04/NF-2026-000184.xml',
        'emitida',
        'ms-fiscal-demo-000184',
        'nfe-demo-vale-000184',
        now(),
        now()
    ),
    (
        (select id from public.vales where observacoes = 'Atendimento tecnico para verificacao de sistema de carga e substituicao sob avaliacao.' limit 1),
        null,
        null,
        'pendente',
        'ms-fiscal-demo-000185',
        'nfe-demo-vale-000185',
        now(),
        now()
    )
on conflict (idempotency_key) do update
set
    vale_id = excluded.vale_id,
    chave_acesso = excluded.chave_acesso,
    xml_path = excluded.xml_path,
    status = excluded.status,
    ms_requisicao_id = excluded.ms_requisicao_id,
    updated_at = now();

insert into public.boletos_orquestrados
    (vale_id, nosso_numero, linha_digitavel, pdf_url, status, identificador_externo, idempotency_key, created_at, updated_at)
values
    (
        (select id from public.vales where observacoes = 'Troca de duas baterias da frota de entregas com recolhimento de sucata.' limit 1),
        '000184998877',
        '23793.38128 60000.018498 98000.184007 8 10470000111980',
        'https://assets.demo.local/boletos/vale-000184.pdf',
        'pago',
        'boleto-demo-000184',
        'boleto-idem-000184',
        now(),
        now()
    ),
    (
        (select id from public.vales where observacoes = 'Reserva de item para cliente final aguardando instalacao.' limit 1),
        '000185554433',
        '34191.79001 01043.510047 91020.150008 1 10490000038990',
        'https://assets.demo.local/boletos/vale-000185.pdf',
        'emitido',
        'boleto-demo-000185',
        'boleto-idem-000185',
        now(),
        now()
    )
on conflict (idempotency_key) do update
set
    vale_id = excluded.vale_id,
    nosso_numero = excluded.nosso_numero,
    linha_digitavel = excluded.linha_digitavel,
    pdf_url = excluded.pdf_url,
    status = excluded.status,
    identificador_externo = excluded.identificador_externo,
    updated_at = now();

insert into public.cnab_remessas
    (tipo_arquivo, nome_arquivo, status, arquivo_path, created_at, updated_at)
values
    (
        'boleto',
        'remessa-cobranca-2026-04-24-demo.rem',
        'gerada',
        'storage/cnab/remessas/remessa-cobranca-2026-04-24-demo.rem',
        now(),
        now()
    )
on conflict do nothing;

insert into public.cnab_retorno_uploads
    (cnab_remessa_id, nome_arquivo, status_processamento, log_processamento, created_at, updated_at)
values
    (
        (select id from public.cnab_remessas where nome_arquivo = 'remessa-cobranca-2026-04-24-demo.rem' limit 1),
        'retorno-cobranca-2026-04-25-demo.ret',
        'processado',
        '1 titulo baixado por pagamento e 1 titulo mantido em aberto no lote demonstrativo.',
        now(),
        now()
    )
on conflict do nothing;

insert into public.filas_contingencia
    (tipo_integracao, payload, tentativas, proxima_tentativa, status, ultimo_erro, idempotency_key, created_at, updated_at)
values
    (
        'fiscal',
        '{
            "vale_observacao":"Atendimento tecnico para verificacao de sistema de carga e substituicao sob avaliacao.",
            "motivo":"aguardando confirmacao manual de serie da bateria",
            "acao":"reemitir_nfe"
        }'::jsonb,
        2,
        now() + interval '30 minutes',
        'pendente',
        'Timeout na resposta do microservico fiscal em ambiente homologacao.',
        'contingencia-fiscal-demo-000185',
        now(),
        now()
    ),
    (
        'bancario',
        '{
            "boleto":"boleto-demo-000185",
            "motivo":"aguardando callback de registro",
            "acao":"consultar_status_boleto"
        }'::jsonb,
        1,
        now() + interval '20 minutes',
        'pendente',
        'Webhook bancario ainda nao recebido.',
        'contingencia-bancaria-demo-000185',
        now(),
        now()
    )
on conflict (idempotency_key) do update
set
    tipo_integracao = excluded.tipo_integracao,
    payload = excluded.payload,
    tentativas = excluded.tentativas,
    proxima_tentativa = excluded.proxima_tentativa,
    status = excluded.status,
    ultimo_erro = excluded.ultimo_erro,
    updated_at = now();

commit;
