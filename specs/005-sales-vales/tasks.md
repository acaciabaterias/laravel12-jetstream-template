# Tasks: Módulo de Vendas e "Vales"

**Feature Branch**: `005-sales-vales`
**Spec File**: [spec.md](spec.md)

## Phase 1: Database & Models (Vales)
- [ ] T001: Criar migration para tabela `vales` (id, cliente_id, vendedor_id, filial_id, status, observacoes, data_faturamento).
- [ ] T002: Criar migration para tabela `itens_vale` (vale_id, bateria_id, qtde, preco_original, preco_final, flag_devolveu_sucata).
- [ ] T003: Criar migrations complementares para `pedidos_venda` e `ordens_servico`.
- [ ] T004: Instanciar Models (`Vale`, `ItemVale`, `PedidoVenda`, `OrdemServico`) com seus devidos relacionamentos relacional e Escopos (`HasFilial`).

## Phase 2: Livewire Components (Point of Sale / Vale)
- [ ] T005: Criar Frontend com Livewire/Tailwind para interface de "Nova Venda / Novo Vale" focada no Balconista.
- [ ] T006: Componente Livewire de adição iterativa de Itens na tabela do Vale (Carrinho de Baterias).
- [ ] T007: Implementar lógica reativa "Net Price" (calculando acréscimo se `flag_devolveu_sucata = FALSE`) baseada no cache da tabela de sucata do Módulo 004.
- [ ] T008: Implementar totalizadores reagindo de forma instantânea às alterações dos Itens na interface.

## Phase 3: Integração de Estoque (Reservas)
- [ ] T009: Criar Service de gestão de Reservas interagindo com as tabelas do Sistema de Estoque (Módulo 004).
- [ ] T010: Disparar registro de quantidade retida "Reservada" transacionalmente no DB ao adicionar Item na UI.
- [ ] T011: Disparar evento de estorno da reserva do banco ao excluir um item do carrinho ou cancelar completamente o Vale.
- [ ] T012: Tratar mensagens de erro da interface caso o item retorne bloqueio por "Saldo Insuficiente".

## Phase 4: Motores de Conversão 
- [ ] T013: Criar Job em Background `ConvertValeToPedidoJob` (Gera Pedido de Venda, confirma saída física no Kardex e credita ou debita "Conta Sucata" do cliente).
- [ ] T014: Criar Job `ConvertValeToOsJob` (Gera OS, reserva estoque, e habilita modo para técnicos editarem seu Laudo).
- [ ] T015: Adicionar Action Buttons na view para viabilizar as conversões ("Faturar Venda", "Enviar para Garantia").

## Phase 5: Buscas e Filtros
- [ ] T016: Desenvolver tela administrativa de listagem do histórico de Vales com filtros otimizados (cliente, período, status, vendedor, filial).
- [ ] T017: Adaptar listagem construindo a tela de forma responsiva focada em suporte offline/cache local para visualização read-only Mobile.

## Phase 6: Edge Cases e Testes Robustos
- [ ] T018: Escrever testes End-to-End simulando concorrência (dois balconistas tentando reservar a mesma e última unidade em Vales diferentes simultaneamente).
- [ ] T019: Teste de verificação se a "Conta Sucata" do cliente tem o débito perfeitamente persistido em uma venda configurada para ausência de devolução física de casco.
- [ ] T020: Teste validando estorno completo (100% dos items) ao confirmar clique de "Cancelar Vale".
