# Tasks: Módulo de Garantias e Feedback

**Feature Branch**: `007-guarantees-feedback`
**Spec File**: [spec.md](spec.md)

## Phase 1: Database & Model Structures
- [ ] T001: Criar migration para `ordem_servico_garantias` garantindo relacionamentos complexos com vales, bateria avaliada, data_abertura e status).
- [ ] T002: Criar migration e Relacionamento `1:1` para `baterias_emprestimo` abrigando as restrições logísticas e de tempo estipuladas.
- [ ] T003: Criar migration para fila analítica de log em `notificacoes_whatsapp` (telefone, status, identificador externo, tracking_error).
- [ ] T004: Adicionar campo numérico complementar na tabela e migration do Módulo 003 (`baterias.kpi_retorno_percent`).
- [ ] T005: Adicionar todas as Políticas (Gates/Policies RBAC do Módulo 002) para acesso restrito aos modelos instanciados.

## Phase 2: Livewire Flow & Service Orders Panel
- [ ] T006: Criar Dashboard do Técnico (Tabela ou Estilo Kanban) exibindo Card das Garantias correntes focadas por `filial_id`.
- [ ] T007: Criar Step Modal de inclusão: Input do Vale Referência permitindo buscar e arrastar os dados fiscais/pedidos automaticamente para a nova O.S local.
- [ ] T008: Implementar Seção de inserção técnica, anexação de arquivo de imagem (Laudo Visual do Multímetro) e a troca entre os botões categóricos de Laudo (Procedente/Improcedente).
- [ ] T009: Inserir pipeline geracional automática do "Vale Avulso" ou Débito Aditivo Financeiro na conta, caso o clique na interface aponte para `Improcedente` engatilhando tarifa de "Serviço Adicional".

## Phase 3: Termos de Empréstimo (SC-GAR-01)
- [ ] T010: Fatiar layout com Flexbox de Impressão HTML5 + Tailwind em `views/pdf/termo_emprestimo.blade.php`.
- [ ] T011: Integrar Controlador / Service para servir o buffer via Package PDF convertendo e devolvendo Content-Disposition download instantâneo `< 2s`.

## Phase 4: WhatsApp Core e Queues 
- [ ] T012: Instalar pacote driver ou montar Http Request formatado para a Gateway de WhatsApp (Contract Service).
- [ ] T013: Fabricar Job Queue Laravel `TriggerGuarantyWhatsAppComm` rodando as variáveis injetadas na mensagem final.
- [ ] T014: Disparar Job a cada salto de Lifecycle do Status da Garantia (Aberto via Observer Models).

## Phase 5: Rebalanceamento de KPIS e Red Flags
- [ ] T015: Injetar classe Event/Observer que recompila o totalizador Global em Banco nativo para extrair a divisão (% Retorno) sempre atrelada a uma chave na tabela base de Cadastros/Produtos (Módulo 003).

## Phase 6: Rotinas Coroutine de Edge Casings e Testes Automatizados
- [ ] T016: Gravar Console Command Schedule Diário que varre na madrugada todas as `data_devolucao_prevista` vencidas, empurrando Alerta Vermelho de "Cobrança/Recuperação Pêndente" aos gerentes.
- [ ] T017: Mocar um envio WhatsApp retornando Network Exception proposital (HTTP 500) e garantir Asserção Unitária de que a O.S fecha independentemente com a log isolada sem falhar e cair a tela do funcionário (500 do servidor).
- [ ] T018: Testar e validar que Módulos de Filial (Multi-Tenant) dividem atritamente relatórios de WhatsApp impedindo SMS cruzados pela franquia.
