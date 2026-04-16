# Feature Specification: Módulo Financeiro Inteligente

**Feature Branch**: `008-intelligent-finance`
**Status**: Ready for Implementation
**Dependencies**: 001-multi-filial-tenant, 002-users-permissions-rbac, 003-structural-registries, 004-inventory-reverse-logistics, 005-sales-vales, 007-guarantees-feedback

## Overview
Gestão automatizada e preditiva das finanças das filiais. Introduz a conciliação automática via banco, projeta o fluxo de caixa, apura a margem de lucro real e consolida integrações vindas das Vendas, Garantias e Logística (vales, OS, recargas de bateria e reembolsos).

## Key Entities
- **ContaBancaria**: (id, filial_id, banco, agencia, conta, tipo, token_api, status)
- **TransacaoFinanceira**: (id, conta_id, tipo [receita/despesa], valor, data, status_conciliado, vale_id, origem)
- **FluxoCaixaProjetado**: (id, filial_id, data, saldo_inicial, total_receber, total_pagar, saldo_projetado)
- **MargemLucroReal**: (id, bateria_id, periodo, valor_venda, custo_aquisicao, frete, imposto, comissao, margem_calculada)

## Functional Requirements
- **FR-FIN-01 - Integração API Bancária**: O sistema DEVE conectar automatizadamente (Open Finance/PIX API), efetuando o match ("Conciliação bancária") entre os recebíveis logísticos/balcão do ERP frente ao Extrato do Banco na nuvem.
- **FR-FIN-02 - Fluxo de Caixa Diário Projetado**: O Painel Financeiro DEVE processar o `saldo em conta + contas a receber - contas a pagar (fornecedor/impostos)` de datas estipuladas, informando furos ou folgas de tesouraria de uma determinada Filial.
- **FR-FIN-03 - Apuração de Margem Real**: Na consolidação do BI de painel, a Gerência DEVE enxergar a margem líquida por item (Mark-up), abatendo custo da sucata reversa, fretes, impostos fiscais e comissões para aquela determinada bateria (Rentabilidade Limpa).
- **FR-FIN-04 - Emissão Condicional C/ Cobrança Garantia**: A geração automática de Faturas/Vales de Recebimento proveniente das O.S de Pós-Venda (Módulo 007) será engatilhada condicional e imperativamente — forçando crédito devido da taxa de recarga para a loja APENAS e logo APÓS o Laudo técnico ser assinalado "Improcedente".

## User Scenarios

### US01: Conciliação Invisível e Rápida (Automação API)
**Given** que o entregador recolheu e faturou 10 vendas via PIX na rua (Módulo 006)
**When** o Gerente Financeiro abrir a Gestão de Transações no dia seguinte
**Then** ele enxerga 10 boletos ou depósitos marcados com "Check Verdinho" já liquidados e validados oficialmente de forma automática, através da Engine Operadora executada em Background pela noite que detectou as liquidações nos comprovantes.

### US02: Painel de Alerta e Previsibilidade
**Given** que há um saldo de R$ 500 reais no cofre da Matriz hoje
**When** a Analista de Contabilidade acessa a aba "Visão de Fim de Semana" no ERP
**Then** o sistema agrupa os custos correntes e vencimentos de boletos previstos, calculando o `Saldo Final Projetado` e disparando um gráfico/grid alertando déficit imediato de operação para sexta-feira.

### US03: Margens de Lucro Brutalmente Claras
**Given** que a Peça Y é precificada por R$ 400 sem análise direta da base
**When** o Diretor seleciona o extrato dessa bateria para compras da Filial A na tabela da MargemReal
**Then** a tabela cruza as integrações: Preço (-400), Impostos (-40), Perda de Casco/Sucata (-45), Comissão do Entregador (-20), apurando e estampando uma % de Margem Final exata do fabricante que previne fechamentos mensais cegos na companhia.

## Edge Cases
- **Boletos sem Referência / Multi-match (API)**: Se existirem depósitos avulsos que a Engine de Conciliação via API não tiver assertividade plena (Valores genéricos e idênticos recebidos no mesmo minuto sem distinções), a Engine Aborta o Match automático e os pendura em colunas "Aguardando Averiguação Humana".
- **Transação Retroativa em Mês Contábil Trancado**: O Módulo NÃO PODE PERMITIR edições corriqueiras (updates/deletes) provindos de diretores/vendedores sobre depósitos pertencentes à competências antigas na qual a Contabilidade e impostos já rodaram fechamento de balanço mensal.

## Success Criteria
- **SC-FIN-01**: A conciliação noturna do script Open Finance deverá atingir ao menos `95% de exatidão` nos matches simples identificados pelo banco.
- **SC-FIN-02**: A engrenagem de consolidação do "Fluxo Projetado" jamais tranca o Front-End, desaguando respostas das queries robustas nas visões analíticas em `< 2.5 Segundos`.
- **SC-FIN-03**: Laudos Improcedentes que sofrerem abortos de comunicação falhando na "Cobrança Automática da OS" sinalizam imediatamente um bloqueio impeditivo para alerta de vazamento humano.
