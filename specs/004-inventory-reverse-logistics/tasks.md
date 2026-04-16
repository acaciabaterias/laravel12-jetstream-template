# Tasks: Módulo de Estoque e Logística Reversa

**Feature Branch**: `004-inventory-reverse-logistics`
**Spec File**: [spec.md](spec.md)

## Phase 1: Database & Models
- [ ] T001: Criar migration para tabela `depositos` (id, nome, filial_id).
- [ ] T002: Criar migration para tabela `estoque_movimentacoes` (id, bateria_id, filial_id, deposito_id, user_id, tipo_operacao, quantidade, justificativa, data).
- [ ] T003: Criar migration para consolidado `estoque_saldos` (bateria_id, filial_id, deposito_id, quantidade_atual).
- [ ] T004: Adicionar campos de crédito de sucata e logística reversa na migration dos models de `Cliente` e `Fornecedor`.
- [ ] T005: Criar os Models (`Deposito`, `EstoqueMovimentacao`, `EstoqueSaldo`) e seus relacionamentos padrões.

## Phase 2: Logística Core e Livewire UI
- [ ] T006: Componentes Livewire 4 para consultar saldo de estoque agregando visões por depósito e filial.
- [ ] T007: Sistema que detecta e acusa baterias de "Shelf Life" vencido com base na data da mais recente movimentação daquele produto.
- [ ] T008: Implementar sistema de Movimentação/Ajuste manual com obrigatoriedade de Log/Justificativa do usuário logado.

## Phase 3: Importação de NFe (XML)
- [ ] T009: Desenvolver Serviço de Parser para extração dos produtos, pesos de sucata e valores das Notas Fiscais Eletrônicas via SimpleXML.
- [ ] T010: Criar interface Livewire/Tailwind de Upload (Drag & Drop) de múltiplos arquivos XML.
- [ ] T011: Criar engine de correspondência (de/para), exibindo tela para o usuário confirmar vínculos de produtos da nota com baterias que existem nativamente no sistema (ERP) se o código de barras for desconhecido.

## Phase 4: A "Conta Sucata"
- [ ] T012: Inserir transações de débito e crédito na Conta de Sucata do Fornecedor após leitura do XML onde constam cascos retornados/devidos.
- [ ] T013: View gerencial das pendências agregadas de Sucata de todas as operações (Filtrado para usuários com acesso de Gestores+).

## Phase 5: Testes Integrados e Edge Cases
- [ ] T014: Mocks & Unit Tests processando um mock XML e garantindo criação correta de movimentações de estoque.
- [ ] T015: Testes de concorrência mitigando a possibilidade de `estoque_movimentacoes` forçar a gravação e deixar a quantidade_atual abaixo de zero no DB.
- [ ] T016: Verificar isolamento Multi-filial do Global Scope evitando que um estoquista veja o caminhão ou a loja da cidade vizinha.
