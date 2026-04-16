# Tasks: Módulo Financeiro Inteligente

**Feature Branch**: `008-intelligent-finance`
**Spec File**: [spec.md](spec.md)

## Phase 1: Database & Model Setup
- [ ] T001: Criar migration fundamental para entidades `contas_bancarias` blindada com constraint obrigatória para o `filial_id`.
- [ ] T002: Criar migration detalhada para `transacoes_financeiras` contendo inputs de Receita/Despesa, Validações Booleanas, e Chaves de Integrações com Extratos/Vales/Pedidos.
- [ ] T003: Criar migrations complementares e Views Estruturais para cachear/agregar a base gerencial de `fluxo_caixa_projetado` e `margens_lucro`.
- [ ] T004: Instanciar Repositórios Iniciais e Models validando Políticas estritas de permissão financeira de Matriz.

## Phase 2: Open Finance and Daily Sync Engine (Jobs)
- [ ] T005: Elaborar HTTP API Client modular para a comunicação das carteiras associadas da loja (`Asaas/Bancos`).
- [ ] T006: Codificar Engine Analítica (`FinanceMatcherProcessor`) que itera entre o JSON recebido com depósitos/vendas logísticas isolando os validados na noite anterior.
- [ ] T007: Incluir Job Scheduler no Kernel de Schedule acionando esse Parser na madrugada com fallback de segurança por falha de Tokens.

## Phase 3: Dashboard & Livewire Views
- [ ] T008: Construção do Componente Front-End `Caixa Operacional` / Adorno Manual de Transações Financeiras (com suporte a lançamentos manuais ou pagamentos locais a prestadores).
- [ ] T009: Criar a UI em formato de gráfico ou grade analítica (Chart/JS Wrapper via Livewire) exibindo visualmente `Fluxo de Caixa Diário` cruzando as contas de Contas A Pagar/Receber.
- [ ] T010: Criar o Grid de KPI interconectado puxando a Tabela Fria que estampa aos gerentes a margem de Mark-Up/Rentabilidades apurada por produto individual.

## Phase 4: Gatilhos Multi-Domínio (Garantias Mód. 007)
- [ ] T011: Elaborar classe de Evento `GuaranteeWasDenied` ou similar escutada pelos Observers que criará artificialmente os faturamentos de serviço obrigatórios (Ex: "Taxa de Recarga OS - X").

## Phase 5: Restrições & Testes Financeiros End-to-End
- [ ] T012: Encriptar lógica de "Data Lock / Fechamento Mensal" onde edições (Updates em Modelos) batem em barreiras impeditivas que geram `Exception HTTP 403`, inviabilizando subversões de pagamentos passados após rodadas fiscais.
- [ ] T013: Mocar dados das Requisições Bancárias em Factory testando a solidez e os índices do Algoritmo de "Matching" contra boletos avulsos maliciosos (Testando o Multi-Match/Invalidação Parcial).
- [ ] T014: Elaboração Exaustiva Unit-Test provando escopo de segurança por Tenants. Usuários de caixas de Cidades Distintas não listam valores em espécie correntes fora do escopo vinculado na API.
