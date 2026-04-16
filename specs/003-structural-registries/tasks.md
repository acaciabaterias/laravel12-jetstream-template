# Tasks: Módulo de Cadastros *(Refatorado)*

**Feature Branch**: `004-structural-registries-v2`
**Spec File**: [spec.md](spec.md)

## Phase 1: Setup *(updated)*

- [ ] T001 Create Laravel migrations for "Fabricante", "Veículo", "Bateria", e "Aplicação" com suporte a multi-filial.
- [ ] T002 Implement database seeder para "Fabricante" com dados de exemplo.
- [ ] T003 Configure Laravel Sail para ambiente Dockerizado.

## Phase 2: Foundational Tasks *(updated)*

- [ ] T004 Implement CRUD operations para "Fabricante" com suporte a multi-filial.
- [ ] T005 Create Livewire components para gerenciar "Fabricante".
- [ ] T006 Design Tailwind CSS-based UI para "Fabricante".
- [ ] T007 Adicionar suporte a atributos dinâmicos para "Bateria" e "Veículo".
- [ ] T008 Implementar exclusão lógica (Soft Delete) com status para todos os cadastros estruturais.
- [ ] T009 Registrar Log de Auditoria Silencioso para todas as operações de CRUD.

## Phase 3: User Stories *(updated)*

### [US1] Vinculação de Produtos por Aplicação
- [ ] T010 Implement search functionality para "Produtos Aplicáveis" no formulário de "Veículo".
- [ ] T011 Add functionality para vincular múltiplas "Baterias" a um "Veículo".
- [ ] T012 Allow adding technical observations para cada "Bateria" vinculada.

### [US2] Validação de Unicidade de Aplicação
- [ ] T013 Add backend validation para impedir duplicatas em "Aplicações".
- [ ] T014 Display error message para entradas duplicadas na UI.

## Phase 4: Edge Cases *(updated)*

- [ ] T015 Validate cloning para mesmo fabricante ou plataforma similar.
- [ ] T016 Ensure batch operations (clonagem/importação) check para duplicatas.
- [ ] T017 Garantir que registros arquivados sejam exibidos como "Inativos" em listagens.

## Phase 5: Final Polish *(updated)*

- [ ] T018 Optimize database queries para grandes volumes de dados.
- [ ] T019 Write PHPUnit tests para todas as funcionalidades implementadas.
- [ ] T020 Conduct performance testing para garantir <200ms response time.

## Phase 6: Validation and Testing *(new)*

- [ ] T021 Validate multi-filial constraints for "Fabricante", "Veículo", "Bateria", e "Aplicação". **Linked to FR12**
- [ ] T022 Write test cases for dynamic attributes in "Bateria" e "Veículo". **Linked to FR13**
- [ ] T023 Ensure edge case handling for "Inativo" status in all operations. **Linked to FR14**
- [ ] T024 Implement audit logging for CRUD operations with traceability. **Linked to FR15**
- [ ] T025 Conduct integration tests for multi-filial support. **Linked to FR12**
- [ ] T026 Conduct performance tests for dynamic attributes configuration. **Linked to FR13**
- [ ] T027 Verify soft delete functionality across all modules. **Linked to FR14**
- [ ] T028 Ensure audit logs are correctly generated and stored. **Linked to FR15**
- [ ] T029 Validate UI consistency for dynamic attributes. **Linked to FR13**
- [ ] T030 Conduct end-to-end tests for CRUD operations. **Linked to FR11, FR12, FR13, FR14, FR15**
- [ ] T031 Perform regression tests to ensure no existing functionality is broken.

## Phase 7: Tarefas FR07 (Múltiplos Produtos por Veículo)

### Backend
- [ ] T032: Criar migration para tabela 'aplicacoes' (veiculo_id, bateria_id, observacao, filial_id)
- [ ] T033: Criar model Application com belongsTo (Veiculo, Bateria)
- [ ] T034: Adicionar validação de unicidade (veiculo_id + bateria_id + filial_id) UNIQUE

### Frontend (aba de aplicações no veículo)
- [ ] T035: Criar componente Livewire para gerenciar aplicações
- [ ] T036: Implementar busca de baterias para adicionar à lista
- [ ] T037: Permitir adicionar observação por aplicação
- [ ] T038: Impedir duplicidade (mesma bateria duas vezes no mesmo veículo)

### Testes
- [ ] T039: Testar adição de múltiplas baterias a um veículo
- [ ] T040: Testar bloqueio de duplicidade
- [ ] T041: Testar remoção de aplicação

## Tarefas FR08 - Clonagem de Aplicações

### Backend
- [ ] T042: Criar método cloneApplications(Veiculo $origem, Veiculo $destino)
- [ ] T043: Validar que veículos são do mesmo fabricante (ou alertar)
- [ ] T044: Copiar aplicações da origem para o destino (evitar duplicatas)

### Frontend
- [ ] T045: Adicionar botão 'Clonar aplicações de...' na tela do veículo
- [ ] T046: Criar modal para selecionar veículo de origem
- [ ] T047: Exibir confirmação com quantidade de aplicações a clonar

### Testes
- [ ] T048: Testar clonagem entre veículos do mesmo fabricante
- [ ] T049: Testar alerta quando fabricantes são diferentes
- [ ] T050: Testar evitar duplicatas ao clonar

## Tarefas FR09 - Busca de Veículos

### Backend
- [ ] T051: Criar endpoint de busca com parâmetros (fabricante, modelo, ano)
- [ ] T052: Implementar cache para lista de fabricantes/modelos (offline mobile)

### Frontend Desktop
- [ ] T053: Criar componente de filtros encadeados (Livewire/Alpine)
- [ ] T054: Implementar busca com debounce

### Frontend Mobile (app entregador)
- [ ] T055: Adaptar busca para tela pequena (input + filtro rápido)
- [ ] T056: Sincronizar cache de fabricantes na inicialização

### Testes
- [ ] T057: Testar busca combinada desktop
- [ ] T058: Testar busca offline no mobile

## Tarefas para FR12 - Validação Multi-Filial

- [ ] T059: Adicionar trait 'MultiTenantScope' que aplica scope global por filial_id
- [ ] T060: Criar middleware 'SetFilialContext' para definir filial atual do usuário
- [ ] T061: Adicionar validação em todos os controllers para verificar se registro pertence à filial do usuário
- [ ] T062: Testar tentativa de acesso a registro de outra filial (deve retornar 403)

## Tarefas para FR15 - Logging de Auditoria

- [ ] T063: Criar migration para tabela 'audit_logs' (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, filial_id)
- [ ] T064: Criar trait 'Auditable' que dispara logs em created/updated/deleted
- [ ] T065: Adicionar interface para visualização de logs (apenas Dono/Gestor)
- [ ] T066: Testar que toda alteração em baterias/veiculos/aplicações gera log

## Tarefas para FR10 - Busca Reversa (Veículos por Bateria)

- [ ] T067: Criar endpoint GET /api/baterias/{id}/veiculos
- [ ] T068: Adicionar aba 'Veículos Aplicáveis' na página de detalhe da bateria
- [ ] T069: Implementar busca com paginação (20 por página)
- [ ] T070: Testar que a busca retorna todos os veículos compatíveis com a bateria

## Tarefas para FR13 - Atributos Dinâmicos

- [ ] T120: Criar migration para 'baterias.atributos_dinamicos' (JSON).
- [ ] T121: Criar migration para 'veiculos.atributos_dinamicos' (JSON).
- [ ] T122: Implementar validação de JSON schema por tipo de bateria.
- [ ] T123: Criar interface de configuração de atributos (Dashboard).
- [ ] T124: Testar cadastro com diferentes tipos de bateria (AGM, Chumbo, Gel).

### Tarefas para Logística Reversa (Princípio IV)
- [ ] T115: Adicionar campos peso_sucata_kg, valor_base_sucata_kg, tem_logistica_reversa na migration baterias.
- [ ] T116: Criar migration para tabela 'estoque_sucata' (id, filial_id, kg_entrada, kg_saida, saldo, ultima_atualizacao).
- [ ] T117: Criar modelo EstoqueSucata com relacionamento com Filial.
- [ ] T118: Implementar evento de incremento de sucata no fechamento da OS/Devolução.
- [ ] T119: Testar fluxo completo: venda sem sucata → acréscimo → descarga → incremento estoque.

## Dependencies *(updated)*

- Phase 1 deve ser concluída antes da Phase 2.
- User Stories (Phase 3) dependem de tarefas fundamentais (Phase 2).
- Edge cases (Phase 4) dependem de User Stories (Phase 3).

## MVP Scope *(updated)*

- Complete Phase 1 e User Story 1 (T001-T012).

## Tarefas para FR16 - Operações em Lote

### Importação em Massa
- [ ] T105: Criar job ImportBateriasJob para processar CSV em chunks de 100
- [ ] T106: Implementar validação de linhas com pulo de erros
- [ ] T107: Adicionar barra de progresso com Livewire

### Exportação em Massa
- [ ] T108: Criar job ExportAplicacoesJob para gerar CSV/Excel
- [ ] T109: Implementar download assíncrono com notificação

### Atualização em Massa
- [ ] T110: Criar job BulkUpdatePrecoJob para reajuste de preços
- [ ] T111: Adicionar suporte a filas (Redis/Beanstalkd)

### Testes
- [ ] T112: Testar importação de 1.000 produtos (< 30 segundos)
- [ ] T113: Testar 5 operações simultâneas
- [ ] T114: Testar recovery de falhas parciais