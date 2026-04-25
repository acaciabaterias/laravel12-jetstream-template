-- Demo data for a tenant operational database.
-- All customers, documents, phone numbers and addresses below are fictional.

begin;

insert into public.users
    (name, email, email_verified_at, password, papel, ativo, ultimo_login, ultimo_ip, remember_token, created_at, updated_at)
values
    (
        'Marcelo Nogueira',
        'marcelo.nogueira+dono@aurorapaulista.demo',
        now() - interval '45 days',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'dono',
        true,
        now() - interval '3 hours',
        '198.51.100.21',
        'demo-owner-token',
        now(),
        now()
    ),
    (
        'Larissa Campos',
        'larissa.campos+gestor@aurorapaulista.demo',
        now() - interval '40 days',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'gestor',
        true,
        now() - interval '6 hours',
        '198.51.100.22',
        'demo-manager-token',
        now(),
        now()
    ),
    (
        'Igor Teixeira',
        'igor.teixeira+vendas@aurorapaulista.demo',
        now() - interval '39 days',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'vendedor',
        true,
        now() - interval '1 hour',
        '198.51.100.23',
        'demo-sales-token',
        now(),
        now()
    ),
    (
        'Paula Siqueira',
        'paula.siqueira+tecnica@aurorapaulista.demo',
        now() - interval '35 days',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'tecnico',
        true,
        now() - interval '2 days',
        '198.51.100.24',
        'demo-tech-token',
        now(),
        now()
    ),
    (
        'Renan Prado',
        'renan.prado+estoque@aurorapaulista.demo',
        now() - interval '32 days',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'estoquista',
        true,
        now() - interval '5 hours',
        '198.51.100.25',
        'demo-stock-token',
        now(),
        now()
    )
on conflict (email) do update
set
    name = excluded.name,
    email_verified_at = excluded.email_verified_at,
    password = excluded.password,
    papel = excluded.papel,
    ativo = excluded.ativo,
    ultimo_login = excluded.ultimo_login,
    ultimo_ip = excluded.ultimo_ip,
    remember_token = excluded.remember_token,
    updated_at = now();

insert into public.clientes
    (nome, tipo_pessoa, documento, email, telefone, celular, cep, endereco, numero, complemento, bairro, cidade, uf, observacoes, ativo, created_at, updated_at)
values
    (
        'Transportadora Serra Azul Ltda.',
        'juridica',
        '45.678.901/0001-28',
        'contasapagar@serraazul.demo',
        '(11) 4002-1200',
        '(11) 98888-1200',
        '04794-000',
        'Avenida do Rio Bonito',
        '980',
        'Galpao B',
        'Socorro',
        'Sao Paulo',
        'SP',
        'Frota leve e utilitarios de entrega urbana.',
        true,
        now(),
        now()
    ),
    (
        'Juliana Ferraz',
        'fisica',
        '286.415.970-08',
        'juliana.ferraz@cliente.demo',
        '(11) 3500-2201',
        '(11) 97777-2201',
        '05846-260',
        'Rua Vicente Leporace',
        '145',
        'Casa 2',
        'Jardim Maracana',
        'Sao Paulo',
        'SP',
        'Cliente final com historico de trocas preventivas.',
        true,
        now(),
        now()
    ),
    (
        'Oficina Torque Norte Centro Automotivo Ltda.',
        'juridica',
        '56.789.012/0001-39',
        'compras@torquenorte.demo',
        '(11) 4321-8899',
        '(11) 96666-8899',
        '02022-001',
        'Rua Voluntarios da Patria',
        '2140',
        'Loja 3',
        'Santana',
        'Sao Paulo',
        'SP',
        'Parceiro recorrente para revenda e instalacoes.',
        true,
        now(),
        now()
    )
on conflict (documento) do update
set
    nome = excluded.nome,
    tipo_pessoa = excluded.tipo_pessoa,
    email = excluded.email,
    telefone = excluded.telefone,
    celular = excluded.celular,
    cep = excluded.cep,
    endereco = excluded.endereco,
    numero = excluded.numero,
    complemento = excluded.complemento,
    bairro = excluded.bairro,
    cidade = excluded.cidade,
    uf = excluded.uf,
    observacoes = excluded.observacoes,
    ativo = excluded.ativo,
    updated_at = now();

insert into public.fabricantes
    (nome, codigo, created_at, updated_at, deleted_at)
values
    ('Volkswagen', 'VW', now(), now(), null),
    ('Fiat', 'FIAT', now(), now(), null),
    ('Chevrolet', 'GM', now(), now(), null)
on conflict do nothing;

insert into public.veiculos
    (fabricante_id, modelo, motorizacao, ano_inicio, ano_fim, atributos_dinamicos, created_at, updated_at, deleted_at)
values
    (
        (select id from public.fabricantes where lower(nome) = lower('Volkswagen') and deleted_at is null limit 1),
        'Saveiro Robust CS',
        '1.6 MSI',
        2021,
        2024,
        '{"segmento":"leve","combustivel":"flex","uso":"entrega"}'::jsonb,
        now(),
        now(),
        null
    ),
    (
        (select id from public.fabricantes where lower(nome) = lower('Fiat') and deleted_at is null limit 1),
        'Strada Freedom CD',
        '1.3 Firefly',
        2022,
        2025,
        '{"segmento":"leve","combustivel":"flex","uso":"frota"}'::jsonb,
        now(),
        now(),
        null
    ),
    (
        (select id from public.fabricantes where lower(nome) = lower('Chevrolet') and deleted_at is null limit 1),
        'Onix LT',
        '1.0 Turbo',
        2020,
        2024,
        '{"segmento":"passeio","combustivel":"flex","uso":"particular"}'::jsonb,
        now(),
        now(),
        null
    )
on conflict do nothing;

insert into public.baterias
    (sku, marca, tecnologia, amperagem, polo, preco_venda, atributos_dinamicos, peso_sucata_kg, valor_base_sucata_kg, tem_logistica_reversa, created_at, updated_at, deleted_at)
values
    (
        'AUR-060-AGM-D',
        'Moura',
        'AGM',
        60,
        'D',
        589.90,
        '{"garantia_meses":18,"cca":540,"linha":"Premium AGM"}'::jsonb,
        13.40,
        4.85,
        true,
        now(),
        now(),
        null
    ),
    (
        'AUR-070-EFB-E',
        'Heliar',
        'EFB',
        70,
        'E',
        649.90,
        '{"garantia_meses":24,"cca":620,"linha":"Start-Stop"}'::jsonb,
        15.10,
        4.95,
        true,
        now(),
        now(),
        null
    ),
    (
        'AUR-045-CONV-D',
        'Bosch',
        'Convencional',
        45,
        'D',
        389.90,
        '{"garantia_meses":12,"cca":390,"linha":"Urbana"}'::jsonb,
        10.20,
        4.60,
        true,
        now(),
        now(),
        null
    )
on conflict (sku) do update
set
    marca = excluded.marca,
    tecnologia = excluded.tecnologia,
    amperagem = excluded.amperagem,
    polo = excluded.polo,
    preco_venda = excluded.preco_venda,
    atributos_dinamicos = excluded.atributos_dinamicos,
    peso_sucata_kg = excluded.peso_sucata_kg,
    valor_base_sucata_kg = excluded.valor_base_sucata_kg,
    tem_logistica_reversa = excluded.tem_logistica_reversa,
    deleted_at = null,
    updated_at = now();

insert into public.aplicacoes
    (veiculo_id, bateria_id, observacao, created_at, updated_at, deleted_at)
values
    (
        (select v.id from public.veiculos v join public.fabricantes f on f.id = v.fabricante_id where f.nome = 'Volkswagen' and v.modelo = 'Saveiro Robust CS' limit 1),
        (select id from public.baterias where sku = 'AUR-060-AGM-D'),
        'Aplicacao recomendada para frota com uso urbano intenso.',
        now(),
        now(),
        null
    ),
    (
        (select v.id from public.veiculos v join public.fabricantes f on f.id = v.fabricante_id where f.nome = 'Fiat' and v.modelo = 'Strada Freedom CD' limit 1),
        (select id from public.baterias where sku = 'AUR-070-EFB-E'),
        'Compatibilidade ideal para sistemas start-stop.',
        now(),
        now(),
        null
    ),
    (
        (select v.id from public.veiculos v join public.fabricantes f on f.id = v.fabricante_id where f.nome = 'Chevrolet' and v.modelo = 'Onix LT' limit 1),
        (select id from public.baterias where sku = 'AUR-045-CONV-D'),
        'Aplicacao de reposicao economica.',
        now(),
        now(),
        null
    )
on conflict (veiculo_id, bateria_id) do update
set
    observacao = excluded.observacao,
    deleted_at = null,
    updated_at = now();

insert into public.depositos
    (nome, tipo, status, created_at, updated_at)
values
    ('Deposito Matriz Sao Paulo', 'principal', 'ativo', now(), now()),
    ('Deposito Tecnico Zona Sul', 'avancado', 'ativo', now(), now())
on conflict (nome) do update
set
    tipo = excluded.tipo,
    status = excluded.status,
    updated_at = now();

insert into public.estoque_saldos
    (bateria_id, deposito_id, quantidade_atual, created_at, updated_at)
values
    (
        (select id from public.baterias where sku = 'AUR-060-AGM-D'),
        (select id from public.depositos where nome = 'Deposito Matriz Sao Paulo'),
        18,
        now(),
        now()
    ),
    (
        (select id from public.baterias where sku = 'AUR-070-EFB-E'),
        (select id from public.depositos where nome = 'Deposito Matriz Sao Paulo'),
        11,
        now(),
        now()
    ),
    (
        (select id from public.baterias where sku = 'AUR-045-CONV-D'),
        (select id from public.depositos where nome = 'Deposito Tecnico Zona Sul'),
        9,
        now(),
        now()
    )
on conflict (bateria_id, deposito_id) do update
set
    quantidade_atual = excluded.quantidade_atual,
    updated_at = now();

insert into public.estoque_movimentacoes
    (bateria_id, deposito_id, user_id, tipo_operacao, origem, quantidade, justificativa, data_movimentacao, created_at, updated_at)
values
    (
        (select id from public.baterias where sku = 'AUR-060-AGM-D'),
        (select id from public.depositos where nome = 'Deposito Matriz Sao Paulo'),
        (select id from public.users where email = 'renan.prado+estoque@aurorapaulista.demo'),
        'entrada',
        'compra_fornecedor',
        20,
        'Reposicao para campanha de revisao preventiva de frotas.',
        now() - interval '10 days',
        now(),
        now()
    ),
    (
        (select id from public.baterias where sku = 'AUR-070-EFB-E'),
        (select id from public.depositos where nome = 'Deposito Matriz Sao Paulo'),
        (select id from public.users where email = 'renan.prado+estoque@aurorapaulista.demo'),
        'entrada',
        'compra_fornecedor',
        12,
        'Lote inicial para linha start-stop.',
        now() - interval '8 days',
        now(),
        now()
    ),
    (
        (select id from public.baterias where sku = 'AUR-045-CONV-D'),
        (select id from public.depositos where nome = 'Deposito Tecnico Zona Sul'),
        (select id from public.users where email = 'renan.prado+estoque@aurorapaulista.demo'),
        'entrada',
        'transferencia_interna',
        10,
        'Separacao para atendimentos em campo.',
        now() - interval '6 days',
        now(),
        now()
    )
on conflict do nothing;

insert into public.vales
    (cliente_id, vendedor_id, status, data_criacao, data_faturamento, observacoes, created_by, created_at, updated_at)
values
    (
        (select id from public.clientes where documento = '45.678.901/0001-28'),
        (select id from public.users where email = 'igor.teixeira+vendas@aurorapaulista.demo'),
        'faturado',
        now() - interval '4 days',
        now() - interval '3 days',
        'Troca de duas baterias da frota de entregas com recolhimento de sucata.',
        (select id from public.users where email = 'larissa.campos+gestor@aurorapaulista.demo'),
        now(),
        now()
    ),
    (
        (select id from public.clientes where documento = '286.415.970-08'),
        (select id from public.users where email = 'igor.teixeira+vendas@aurorapaulista.demo'),
        'aberto',
        now() - interval '1 day',
        null,
        'Reserva de item para cliente final aguardando instalacao.',
        (select id from public.users where email = 'marcelo.nogueira+dono@aurorapaulista.demo'),
        now(),
        now()
    ),
    (
        (select id from public.clientes where documento = '56.789.012/0001-39'),
        (select id from public.users where email = 'igor.teixeira+vendas@aurorapaulista.demo'),
        'em_atendimento',
        now() - interval '12 hours',
        null,
        'Atendimento tecnico para verificacao de sistema de carga e substituicao sob avaliacao.',
        (select id from public.users where email = 'larissa.campos+gestor@aurorapaulista.demo'),
        now(),
        now()
    )
on conflict do nothing;

insert into public.itens_vale
    (vale_id, bateria_id, quantidade, preco_unitario_original, preco_unitario_final, flag_devolveu_sucata, observacao, created_at, updated_at)
values
    (
        (select id from public.vales where observacoes = 'Troca de duas baterias da frota de entregas com recolhimento de sucata.' limit 1),
        (select id from public.baterias where sku = 'AUR-060-AGM-D'),
        2,
        589.90,
        559.90,
        true,
        'Desconto aplicado pela devolucao de sucata equivalente.',
        now(),
        now()
    ),
    (
        (select id from public.vales where observacoes = 'Reserva de item para cliente final aguardando instalacao.' limit 1),
        (select id from public.baterias where sku = 'AUR-045-CONV-D'),
        1,
        389.90,
        389.90,
        false,
        'Cliente ainda nao trouxe bateria antiga.',
        now(),
        now()
    ),
    (
        (select id from public.vales where observacoes = 'Atendimento tecnico para verificacao de sistema de carga e substituicao sob avaliacao.' limit 1),
        (select id from public.baterias where sku = 'AUR-070-EFB-E'),
        1,
        649.90,
        629.90,
        true,
        'Preco promocional para cliente parceiro.',
        now(),
        now()
    )
on conflict do nothing;

insert into public.reservas_estoque
    (vale_id, item_vale_id, bateria_id, deposito_id, quantidade, status, created_at, updated_at)
values
    (
        (select id from public.vales where observacoes = 'Troca de duas baterias da frota de entregas com recolhimento de sucata.' limit 1),
        (select iv.id from public.itens_vale iv join public.vales v on v.id = iv.vale_id where v.observacoes = 'Troca de duas baterias da frota de entregas com recolhimento de sucata.' limit 1),
        (select id from public.baterias where sku = 'AUR-060-AGM-D'),
        (select id from public.depositos where nome = 'Deposito Matriz Sao Paulo'),
        2,
        'separada',
        now(),
        now()
    ),
    (
        (select id from public.vales where observacoes = 'Reserva de item para cliente final aguardando instalacao.' limit 1),
        (select iv.id from public.itens_vale iv join public.vales v on v.id = iv.vale_id where v.observacoes = 'Reserva de item para cliente final aguardando instalacao.' limit 1),
        (select id from public.baterias where sku = 'AUR-045-CONV-D'),
        (select id from public.depositos where nome = 'Deposito Tecnico Zona Sul'),
        1,
        'reservada',
        now(),
        now()
    ),
    (
        (select id from public.vales where observacoes = 'Atendimento tecnico para verificacao de sistema de carga e substituicao sob avaliacao.' limit 1),
        (select iv.id from public.itens_vale iv join public.vales v on v.id = iv.vale_id where v.observacoes = 'Atendimento tecnico para verificacao de sistema de carga e substituicao sob avaliacao.' limit 1),
        (select id from public.baterias where sku = 'AUR-070-EFB-E'),
        (select id from public.depositos where nome = 'Deposito Matriz Sao Paulo'),
        1,
        'reservada',
        now(),
        now()
    )
on conflict do nothing;

insert into public.pedidos_venda
    (vale_id, cliente_id, data_emissao, valor_total, status, nf_referencia, created_at, updated_at)
values
    (
        (select id from public.vales where observacoes = 'Troca de duas baterias da frota de entregas com recolhimento de sucata.' limit 1),
        (select id from public.clientes where documento = '45.678.901/0001-28'),
        now() - interval '3 days',
        1119.80,
        'faturado',
        'NF-2026-000184',
        now(),
        now()
    )
on conflict do nothing;

insert into public.ordens_servico
    (vale_id, cliente_id, tecnico_responsavel_id, data_abertura, status, laudo, observacoes, created_at, updated_at)
values
    (
        (select id from public.vales where observacoes = 'Atendimento tecnico para verificacao de sistema de carga e substituicao sob avaliacao.' limit 1),
        (select id from public.clientes where documento = '56.789.012/0001-39'),
        (select id from public.users where email = 'paula.siqueira+tecnica@aurorapaulista.demo'),
        now() - interval '10 hours',
        'em_diagnostico',
        'Alternador com oscilacao leve. Bateria atual apresenta tensao de repouso abaixo do esperado.',
        'Cliente solicitou laudo completo antes da aprovacao final da troca.',
        now(),
        now()
    ),
    (
        (select id from public.vales where observacoes = 'Troca de duas baterias da frota de entregas com recolhimento de sucata.' limit 1),
        (select id from public.clientes where documento = '45.678.901/0001-28'),
        (select id from public.users where email = 'paula.siqueira+tecnica@aurorapaulista.demo'),
        now() - interval '4 days',
        'concluida',
        'Substituicao realizada e sistema de carga validado dentro do padrao.',
        'OS encerrada com recolhimento de duas unidades usadas.',
        now(),
        now()
    )
on conflict do nothing;

insert into public.conta_sucata_movimentacoes
    (entidade_tipo, entidade_id, tipo_movimento, quantidade_kg, valor_unitario, saldo_resultante, origem, created_at, updated_at)
values
    (
        'cliente',
        (select id from public.clientes where documento = '45.678.901/0001-28'),
        'credito',
        26.80,
        4.85,
        129.98,
        'vale_faturado',
        now() - interval '3 days',
        now()
    ),
    (
        'cliente',
        (select id from public.clientes where documento = '56.789.012/0001-39'),
        'credito',
        15.10,
        4.95,
        74.75,
        'avaliacao_tecnica',
        now() - interval '6 hours',
        now()
    )
on conflict do nothing;

commit;
