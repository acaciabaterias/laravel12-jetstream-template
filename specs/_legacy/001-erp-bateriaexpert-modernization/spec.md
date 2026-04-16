# Feature Specification: ERP BateriaExpert (Modernização)

**Feature Branch**: `001-erp-bateriaexpert-modernization`  
**Created**: 11 de abril de 2026  
**Status**: Draft  
**Input**: User description: "BRIEFING DE SOFTWARE: ERP BateriaExpert (Modernização)\nVisão Geral da Solução:\nUm ERP especialista em gestão de revendas de baterias automotivas, focado na modernização de um fluxo de trabalho validado há 27 anos. O sistema substitui a complexidade de planilhas e softwares genéricos por uma solução focada em logística reversa de sucata, mobilidade para entregadores e automação financeira via microserviços.\nO Problema:\nDificuldade em encontrar softwares que entendam a regra de negócio específica do ramo (controle de peso de sucata e logística reversa). Sistemas atuais são genéricos, dependem de muita alimentação manual e não integram a rua (entregador) com o balcão.\nPúblico-Alvo:\nDonos de lojas de baterias, balconistas, gestores financeiros e entregadores/motoboys.\nModelo de Precificação e Negócio:\nSoftware como Serviço (SaaS) com assinatura mensal.\nPerfis de Acesso:\nAdministrador/Gestor: Acesso total, relatórios de margem real e decisões de compra.\nBalconista: Cadastro de clientes, vendas, orçamentos e consultas de estoque.\nFinanceiro: Conciliação bancária, fluxo de caixa e contas a pagar/receber.\nEntregador: Acesso via celular para rotas, ajustes de peso de sucata e recebimentos.\nMódulos e Casos de Uso (O que o sistema faz):\nMódulo de Cadastros Estruturais\nUsuário gerencia Clientes, Fornecedores e Vendedores.\nSistema cataloga Produtos (Baterias) com atributos técnicos (Amperagem, Polo, Marca, Tecnologia).\nGestor controla Veículos da frota e Plano de Contas/Centros de Custo.\nMódulo de Vendas e "Vales"\nBalconista cria Vales (pedidos abertos) que permitem alterações antes do faturamento.\nSistema calcula acréscimo automático no preço caso não haja devolução de sucata (baseado na tabela de peso).\nBalconista converte Vale em Pedido de Venda ou Ordem de Serviço.\nMódulo de Logística (App do Entregador)\nEntregador acessa rota de entregas pelo celular.\nEntregador edita o peso/presença da sucata no ato da entrega para ajustar o valor total.\nA loja acompanha, em tempo real, a movimentação da rota de entrega. \nO Entregador pode registrar recebimentos (Pix, Cartão, Dinheiro) e o pedido é finalizado pela loja apenas depois que receber a sucata do entregador.\nMódulo de Estoque e Compras (Logística Reversa)\nSistema importa XML de fornecedores para entrada de estoque automática.\nSistema gerencia a "Conta de Sucata" (créditos de peso) com clientes e fornecedores.\nGestor monitora o "Tempo de Prateleira" das baterias para evitar perda de carga.\nMódulo Financeiro Inteligente\nSistema realiza conciliação bancária automática via API (sem input manual).\nGestor visualiza margem de lucro real por produto (abatendo impostos e custos de sucata).\nSistema integra com microserviço para emissão de boletos e baixa automática.\nMódulo de Garantias e Feedback\nTécnico abre Ordem de Serviço de Garantia podendo ser vinculada à venda original e podendo ser vinculada também ao cliente a fim de rastrear recorrências.\nSistema gerencia empréstimo de baterias de reserva.\nSistema envia notificações automáticas via WhatsApp ao cliente sobre cada mudança de status de sua garantia (ex: "Recebido da fábrica").\nO sistema emite relatório de garantias por marca, modelo e tempo entre a compra e a reclamação do cliente, com o objetivo de fornecer ao gestor controle de qualidade do produto, estabelecendo dinamicamente um índice de retorno, que fará  parte, automaticamente, do cadastro do produto.\nMódulo Fiscal\nSistema comunica-se com microserviço para emissão de Cupom Fiscal (PDV) e NF-e.\nUsuário consulta, imprime, faz correções, cancela notas e emite relatórios contábeis diretamente pela interface do ERP."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Gestão de Cadastros Estruturais (Priority: P1)

Permite aos usuários (Administrador/Gestor) gerenciar clientes, fornecedores, vendedores, produtos (baterias com atributos técnicos), veículos da frota e planos de contas/centros de custo, fornecendo a base de dados para todo o sistema.

**Why this priority**: Fundamental para o funcionamento do ERP, pois todos os outros módulos dependem de dados estruturais bem definidos.

**Independent Test**: Pode ser totalmente testado criando, editando e visualizando clientes, fornecedores, vendedores, produtos (baterias), veículos e contas.

**Acceptance Scenarios**:

1.  **Given** um Administrador/Gestor, **When** ele acessa o módulo de cadastros estruturais, **Then** ele pode gerenciar (criar, editar, visualizar, excluir) Clientes, Fornecedores e Vendedores.
2.  **Given** um Administrador/Gestor, **When** ele cadastra um novo Produto (Bateria), **Then** o sistema permite especificar atributos técnicos como Amperagem, Polo, Marca e Tecnologia.
3.  **Given** um Administrador/Gestor, **When** ele acessa o módulo de cadastros estruturais, **Then** ele pode controlar os Veículos da frota e definir Plano de Contas/Centros de Custo.

---

### User Story 2 - Gestão de Vendas e Vales (Priority: P1)

Permite aos balconistas criar "Vales" (pedidos abertos) que podem ser alterados antes do faturamento, calcula acréscimos automáticos no preço caso não haja devolução de sucata e permite a conversão de "Vales" em Pedidos de Venda ou Ordens de Serviço.

**Why this priority**: Processo de negócio central; impacta diretamente a geração de receita e o atendimento de pedidos.

**Independent Test**: Pode ser totalmente testado criando vales, convertendo-os em pedidos de venda ou ordens de serviço e verificando os ajustes automáticos de preço para sucata.

**Acceptance Scenarios**:

1.  **Given** um Balconista, **When** ele cria um novo "Vale" (pedido aberto), **Then** o sistema permite modificações antes do faturamento.
2.  **Given** um Balconista, **When** ele cria um "Vale" sem devolução de sucata, **Then** o sistema calcula e aplica automaticamente um acréscimo de preço com base na tabela de peso da sucata.
3.  **Given** um Balconista, **When** um "Vale" está pronto, **Then** ele pode convertê-lo em um Pedido de Venda ou Ordem de Serviço.

---

### User Story 3 - Logística de Entregas e Mobilidade (Priority: P1)

Permite que entregadores acessem rotas pelo celular, editem o peso/presença da sucata no local da entrega, registrem recebimentos e permite à loja acompanhar em tempo real as movimentações de rota.

**Why this priority**: Aborda um ponto crítico de integração das operações de campo com o balcão, melhorando a eficiência e a precisão dos dados.

**Independent Test**: Pode ser totalmente testado por um entregador acessando rotas, ajustando peso/presença da sucata, registrando pagamentos em um dispositivo móvel e a loja visualizando os movimentos da rota em tempo real.

**Acceptance Scenarios**:

1.  **Given** um Entregador, **When** ele acessa o sistema via celular, **Then** ele pode visualizar suas rotas de entrega atribuídas.
2.  **Given** um Entregador no local da entrega, **When** ele edita o peso/presença da sucata, **Then** o sistema ajusta o valor total do pedido dinamicamente.
3.  **Given** um Entregador, **When** ele registra um recebimento (Pix, Cartão, Dinheiro) no local, **Then** o sistema registra o recebimento, e a loja pode finalizar o pedido somente após receber a sucata do entregador.
4.  **Given** um Balconista ou Administrador/Gestor, **When** um Entregador está em uma rota, **Then** a loja pode monitorar os movimentos da rota de entrega em tempo real.

---

### User Story 4 - Gestão de Estoque e Compras com Logística Reversa (Priority: P2)

Permite a importação automática de XML de fornecedores para entrada de estoque, gerencia a "Conta de Sucata" (créditos de peso) com clientes e fornecedores e monitora o "Tempo de Prateleira" das baterias para evitar perda de carga.

**Why this priority**: Essencial para gerenciar o estoque, otimizar compras e lidar com a logística reversa exclusiva da sucata.

**Independent Test**: Pode ser totalmente testado importando XML, gerenciando contas de sucata e verificando o monitoramento da vida útil.

**Acceptance Scenarios**:

1.  **Given** um Gestor, **When** ele recebe novo estoque, **Then** o sistema pode importar automaticamente arquivos XML de fornecedores para entrada de estoque.
2.  **Given** o sistema, **When** uma transação envolve sucata, **Then** ele gerencia uma "Conta de Sucata" (créditos de peso) com clientes e fornecedores.
3.  **Given** um Gestor, **When** visualizando o estoque, **Then** o sistema monitora o "Tempo de Prateleira" das baterias para evitar perda de carga.

---

### User Story 5 - Gestão Financeira Inteligente (Priority: P2)

Automatiza a conciliação bancária via API, visualiza a margem de lucro real por produto e integra com microserviços para emissão de boletos e baixa automática.

**Why this priority**: Automatiza tarefas financeiras cruciais, reduz o esforço manual e fornece insights precisos sobre a lucratividade.

**Independent Test**: Pode ser totalmente testado verificando a conciliação bancária automática, o cálculo da margem real e a emissão/baixa automática de boletos via microserviços.

**Acceptance Scenarios**:

1.  **Given** o sistema, **When** extratos bancários estão disponíveis, **Then** ele realiza conciliação bancária automática via API sem entrada manual.
2.  **Given** um Gestor, **When** visualizando o desempenho do produto, **Then** o sistema exibe margens de lucro reais por produto, deduzindo impostos e custos de sucata.
3.  **Given** um Balconista ou Gestor, **When** um pagamento precisa ser feito, **Then** o sistema se integra a um microserviço para emissão de boletos e baixa automática.

---

### User Story 6 - Gestão de Garantias e Feedback (Priority: P2)

Permite a abertura de Ordens de Serviço de Garantia vinculadas à venda original e ao cliente, gerencia o empréstimo de baterias de reserva, envia notificações automáticas via WhatsApp e emite relatórios de garantia para controle de qualidade.

**Why this priority**: Melhora o atendimento ao cliente, rastreia a qualidade do produto e fornece feedback valioso para a melhoria do produto.

**Independent Test**: Pode ser totalmente testado abrindo ordens de serviço, gerenciando baterias de empréstimo, recebendo notificações do WhatsApp e gerando relatórios de qualidade.

**Acceptance Scenarios**:

1.  **Given** um Técnico, **When** um cliente traz uma bateria para garantia, **Then** ele pode abrir uma Ordem de Serviço de Garantia vinculada à venda original e ao cliente para rastrear recorrências.
2.  **Given** um cliente precisa de uma bateria temporária, **Then** o sistema gerencia o empréstimo de baterias de reserva.
3.  **Given** uma ordem de serviço de garantia muda de status, **Then** o sistema envia notificações automáticas via WhatsApp ao cliente.
4.  **Given** um Gestor deseja insights de qualidade, **Then** o sistema gera um relatório de garantia por marca, modelo e tempo entre a compra e a reclamação, estabelecendo dinamicamente um índice de retorno para o cadastro do produto.

---

### User Story 7 - Módulo Fiscal Integrado (Priority: P3)

Comunica-se com microserviços para emissão de Cupom Fiscal (PDV) e NF-e, e permite ao usuário consultar, imprimir, corrigir, cancelar notas e emitir relatórios contábeis diretamente pela interface do ERP.

**Why this priority**: Garante a conformidade legal e simplifica as operações fiscais, embora possa depender de outros módulos centrais estarem estáveis.

**Independent Test**: Pode ser totalmente testado verificando a comunicação com microserviços para emissão de documentos fiscais e a capacidade do usuário de gerenciar (consultar, imprimir, corrigir, cancelar) esses documentos e relatórios.

**Acceptance Scenarios**:

1.  **Given** uma venda exige um documento fiscal, **Then** o sistema se comunica com um microserviço para emitir um Cupom Fiscal (PDV) e NF-e.
2.  **Given** um usuário precisa gerenciar documentos fiscais, **Then** ele pode consultar, imprimir, corrigir e cancelar notas diretamente pela interface do ERP.
3.  **Given** um contador precisa de relatórios, **Then** o usuário pode gerar relatórios contábeis diretamente da interface do ERP.

## Requirements *(mandatory)*

### Functional Requirements

-   **FR-001**: O sistema DEVE permitir aos usuários gerenciar Clientes, Fornecedores e Vendedores.
-   **FR-002**: O sistema DEVE permitir o cadastramento de Produtos (Baterias) com atributos técnicos (Amperagem, Polo, Marca, Tecnologia).
-   **FR-003**: O sistema DEVE permitir o gerenciamento de Veículos da frota e Plano de Contas/Centros de Custo.
-   **FR-004**: O sistema DEVE permitir que Balconistas criem "Vales" (pedidos abertos) editáveis antes do faturamento.
-   **FR-005**: O sistema DEVE calcular e adicionar automaticamente um acréscimo de preço para "Vales" sem devolução de sucata com base em uma tabela de peso.
-   **FR-006**: O sistema DEVE permitir que Balconistas convertam um "Vale" em um Pedido de Venda ou Ordem de Serviço.
-   **FR-007**: O sistema DEVE fornecer aos Entregadores acesso às rotas de entrega via aplicativo móvel.
-   **FR-008**: O sistema DEVE permitir que Entregadores editem o peso/presença da sucata no ponto de entrega para ajustar o valor total.
-   **FR-009**: O sistema DEVE fornecer rastreamento em tempo real dos movimentos da rota de entrega para a loja.
-   **FR-010**: O sistema DEVE permitir que Entregadores registrem pagamentos (Pix, Cartão, Dinheiro) em seu dispositivo móvel.
-   **FR-011**: O sistema DEVE permitir que as lojas finalizem os pedidos somente após receber a sucata do Entregador.
-   **FR-012**: O sistema DEVE importar automaticamente arquivos XML de fornecedores para entrada de estoque.
-   **FR-013**: O sistema DEVE gerenciar uma "Conta de Sucata" (créditos de peso) com clientes e fornecedores.
-   **FR-014**: O sistema DEVE monitorar o "Tempo de Prateleira" das baterias para evitar perda de carga.
-   **FR-015**: O sistema DEVE realizar conciliação bancária automática via API.
-   **FR-016**: O sistema DEVE exibir margens de lucro reais por produto, deduzindo impostos e custos de sucata.
-   **FR-017**: O sistema DEVE integrar-se a um microserviço para emissão de boletos e baixa automática.
-   **FR-018**: O sistema DEVE permitir que Técnicos abram Ordens de Serviço de Garantia vinculadas às vendas originais e aos clientes.
-   **FR-019**: O sistema DEVE gerenciar o empréstimo de baterias de reserva.
-   **FR-020**: O sistema DEVE enviar notificações automáticas via WhatsApp aos clientes para alterações de status de garantia.
-   **FR-021**: O sistema DEVE gerar relatórios de garantia por marca, modelo e tempo até a reclamação, estabelecendo dinamicamente um índice de retorno para o cadastro do produto.
-   **FR-022**: O sistema DEVE se comunicar com microserviços para emissão de Cupom Fiscal (PDV) e NF-e.
-   **FR-023**: O sistema DEVE permitir aos usuários consultar, imprimir, corrigir e cancelar notas fiscais via interface ERP.
-   **FR-024**: O sistema DEVE permitir aos usuários emitir relatórios contábeis via interface ERP.
-   **FR-025**: O sistema DEVE fornecer controle de acesso para diferentes perfis de usuário: Administrador/Gestor, Balconista, Financeiro, Entregador.
-   **FR-026**: O sistema DEVE operar como Software as a Service (SaaS) com um modelo de assinatura mensal.

### Key Entities *(include if feature involves data)*

-   **Cliente**: Representa um cliente do negócio de revenda de baterias automotivas. Atributos chave: nome, informações de contato, endereço, saldo da conta de sucata.
-   **Fornecedor**: Representa um fornecedor de baterias ou um destinatário de sucata. Atributos chave: nome, informações de contato, saldo da conta de sucata.
-   **Vendedor**: Representa um vendedor. Atributos chave: nome, informações de contato.
-   **Produto (Bateria)**: Representa uma bateria automotiva. Atributos chave: nome, descrição, amperagem, polo, marca, tecnologia, preço, quantidade em estoque, status de tempo de prateleira, índice de retorno.
-   **Veículo**: Representa um veículo da frota de entrega. Atributos chave: placa, modelo, capacidade, status atual.
-   **Plano de Contas/Centro de Custo**: Entidades de classificação financeira. Atributos chave: nome, código, tipo.
-   **Vale (Pedido Aberto)**: Representa um pedido aberto que pode ser modificado antes da faturação final. Atributos chave: cliente, produtos, quantidades, preço, status da sucata, pedido de venda/serviço associado.
-   **Pedido de Venda**: Um pedido de venda finalizado. Atributos chave: cliente, produtos, quantidades, preço total, status de pagamento, "Vale" associado.
-   **Ordem de Serviço**: Uma ordem de serviço, potencialmente para garantia ou outros serviços. Atributos chave: cliente, detalhes do serviço, status, "Vale" associado ou venda original.
-   **Sucata**: Representa baterias de sucata. Atributos chave: peso, tipo, cliente/fornecedor associado, valor do crédito.
-   **Rota de Entrega**: Uma rota de entrega atribuída a um Entregador. Atributos chave: entregador, lista de pedidos, status, localização em tempo real (se disponível).
-   **Recebimento**: Um pagamento recebido. Atributos chave: valor, método de pagamento (Pix, Cartão, Dinheiro), pedido associado, data.
-   **Garantia**: Uma reclamação de garantia. Atributos chave: cliente, produto, referência de venda original, problema relatado, status, referência da ordem de serviço.
-   **Microserviço Financeiro**: Serviço externo para conciliação bancária, emissão de boletos e baixa.
-   **Microserviço Fiscal**: Serviço externo para emissão de Cupom Fiscal (PDV) e NF-e.

## Success Criteria *(mandatory)*

### Measurable Outcomes

-   **SC-001**: Reduzir a entrada manual de dados para conciliação financeira em 90% através da integração de API.
-   **SC-002**: Aumentar a precisão dos registros de estoque de baterias e sucata em 95%.
-   **SC-003**: Reduzir o tempo de conclusão da rota de entrega em 15% devido ao acesso móvel e atualizações em tempo real.
-   **SC-004**: Atingir 100% de conformidade com a emissão de documentos fiscais (Cupom Fiscal, NF-e) via integração de microserviços.
-   **SC-005**: Melhorar a satisfação do cliente com os processos de garantia fornecendo notificações automáticas via WhatsApp e tempos de resolução mais rápidos.
-   **SC-006**: Permitir que os Gestores visualizem margens de lucro de produtos em tempo real, incluindo todos os custos, para 100% dos produtos.
-   **SC-007**: Reduzir os erros relacionados ao controle de peso da sucata e logística reversa em 80%.
-   **SC-008**: O sistema processa com sucesso 100% dos arquivos XML de fornecedores para entrada automática de estoque.

### Edge Cases

-   O que acontece quando um entregador tenta registrar sucata para um pedido que não a espera?
-   Como o sistema lida com devoluções parciais de sucata ou discrepâncias no peso da sucata?
-   O que acontece se um microsserviço para operações financeiras ou fiscais estiver temporariamente indisponível?
-   Como o sistema gerencia o estoque quando o "Tempo de Prateleira" de um produto expira?
-   E se um cliente tentar reivindicar uma garantia para um produto não vinculado a nenhuma venda?