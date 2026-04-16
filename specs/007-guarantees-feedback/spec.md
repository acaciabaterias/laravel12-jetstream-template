# Feature Specification: Módulo de Garantias e Feedback

**Feature Branch**: `007-guarantees-feedback`
**Status**: Ready for Implementation
**Dependencies**: 001-multi-filial-tenant, 002-users-permissions-rbac, 003-structural-registries, 004-inventory-reverse-logistics, 005-sales-vales

## Overview
Gestão completa do ciclo de vida pós-venda, especificamente tratando assistências técnicas (Garantia), fornecimento de baterias de empréstimo (backup ativo) e alertas ao consumidor via WhatsApp. Adicionalmente, este módulo alimenta uma métrica vital de Qualidade (Índice de Retorno) retroalimentando o cadastro de produtos.

## Key Entities
- **OrdemServicoGarantia**: (id, cliente_id, bateria_id, vale_original_id, data_abertura, status, laudo, resultado_procedente_improcedente)
- **BateriaEmprestimo**: (id, os_garantia_id, bateria_usada_id, data_retirada, data_devolucao_prevista, data_devolucao_real, termo_gerado)
- **NotificacaoWhatsApp**: (id, os_garantia_id, cliente_telefone, status, mensagem, data_envio)
- **IndiceRetorno**: (id, bateria_id, periodo, total_vendidas, total_garantias, indice_calculado)

## Functional Requirements
- **FR-GAR-01 - Abertura Híbrida de O.S.**: O técnico ou logista DEVE conseguir abrir uma OS de Garantia vinculada nativamente a um Vale anterior (Venda) ou avulsa (caso não tenha o Vale sistêmico em mãos).
- **FR-GAR-02 - Empréstimo de Backup Ativo**: O sistema DEVE guiar o check-out de uma bateria usada do estoque para o cliente não ficar a pé, produzindo e armazenando a assinatura de um "Termo de Responsabilidade" em PDF auto-preenchido.
- **FR-GAR-03 - Notificações Autônomas**: Sempre que a OS de garantia transacionar de status (ex: "Em Avaliação", "Pronta para Retirada", "Negada"), o sistema DEVE postar os dados de comunicação para disparo via Gateway de WhatsApp.
- **FR-GAR-04 - Qualidade / Índice de Retorno**: O sistema DEVE recomputar automaticamente a correlação entre Volume Lido de Vendas (005) vs Quantidade de Garantias (007) gerando o `Índice de Retorno`, salvando este dado dinâmico atrelado ao módulo Cadastro (003) do respectivo produto, visível para análise de compras.
- **FR-GAR-05 - Cobrança de Improcedência**: Se o técnico avaliar que a quebra não é do produto e emitir Laudo "Improcedente", a OS de Garantia DEVE lançar integralmente uma cobrança de recarga/serviço no painel ou Vale de taxa correspondente, não deixando a mão de obra sair gratuita.

## User Scenarios

### US01: Abertura e Empréstimo com Termo
**Given** que um cliente volta à loja 3 meses pós-compra alegando defeito na bateria
**When** o Atendente abre uma OS de Garantia vinculada à Venda e disponibiliza uma Bateria 60Ah provisória para ele ir embora
**Then** o sistema reserva a Bateria Provisória no inventário Logístico e disponibiliza instantaneamente um Termo de Compromisso em PDF populado com as credenciais do cliente e a data obrigatória de devolução daquele bem.

### US02: Laudo e Notificação de Improcedência
**Given** que a bateria analisada em bancada técnica encontrava-se apenas descarregada por falha do veículo
**When** o Técnico aponta no painel que o Laudo é "Improcedente - Apenas Descarregada"
**Then** o sistema gera o encargo de Recarga no Painel Financeiro/Caixa e envia um WhatsApp padronizado: "Seu laudo de Bateria saiu! Improcedente. Taxa: R$ 30,00. Retirada em nossa base."

### US03: KPI de Cadastro e Red Flags P/ Compras
**Given** que o Gerente decide iniciar encomenda com o Fabricante 
**When** ele pesquisa pelo detalhamento da "Bateria Turbo X"
**Then** o painel de Cadastro avisa ostensivamente: "Índice de Garantia/Retorno: 14%", alertando que de cada 100 vendas nos últimos x meses, o fornecedor está entregando uma quebra altíssima, bloqueando assim compras negligentes.

## Edge Cases
- **Bateria Provisória não Devolvida no Prazo**: Se expirar a "Data Devolução Prevista" da BateriaEmprestimo, o sistema DEVE acoplar na homepage de Alertas Críticos da gerência um pop-up imperativo, além de encadear push notification pro Cliente via WhatsApp de vencimento.
- **Número Celular Inválido ou Mudo**: Operações com telefone incorreto (ex: sem DDD) devem abafar e contornar a falha da notificação autônoma de forma silenciosa, marcando "Falha" interna na OS sem corromper nenhuma cadeia no salvamento dos laudos pelo mecânico.

## Success Criteria
- **SC-GAR-01**: A impressão do Termo de Empréstimo PDF deve ser inferida pelo servidor e entregue na tela em até `< 2 segundos` absolutos após ser requerida.
- **SC-GAR-02**: Todas as Notificações de WhatsApp operam paralelamente com um delay de envio programado em Background que nunca engaveta ou tranca a interface Web de quem clicou em Salvar (desempenho).
- **SC-GAR-03**: O "Índice de Retorno KPI" flutua em tempo real refletindo atualizações cruzadas do Banco com impacto e complexidade processual avaliada em `O(1)` na leitura da tela de produtos, inviabilizando gargalos de relatórios antigos.
