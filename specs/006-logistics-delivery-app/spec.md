# Feature Specification: Módulo de Logística e App do Entregador

**Feature Branch**: `006-logistics-delivery-app`
**Status**: Ready for Implementation
**Dependencies**: 001-multi-filial-tenant, 002-users-permissions-rbac, 003-structural-registries, 004-inventory-reverse-logistics, 005-sales-vales

## Overview
Permite que entregadores acessem rotas de entrega pelo celular, editem pedidos em função do peso real da sucata coletada no local de entrega, registrem recebimentos (pagamentos), e a loja acompanhe as coordenadas em tempo real. O fechamento final do pedido e saída efetiva do estoque ocorrem somente após a confirmação sistêmica do recebimento físico e devolução da bateria inservível (sucata).

## Key Entities
- **RotaEntrega**: (id, entregador_id, filial_id, data_rota, status, veiculo_id)
- **PontoEntrega**: (id, rota_id, vale_id, cliente_id, endereco, ordem, status, peso_sucata_coletado, observacao)
- **Geolocalizacao**: (id, ponto_entrega_id, latitude, longitude, timestamp)
- **RecebimentoMovel**: (id, ponto_entrega_id, valor, metodo [pix/cartao/dinheiro], status_sincronizado)

## Functional Requirements
- **FR-LOG-01 - Sincronicidade Offline**: O sistema PWA ou aplicativo do entregador DEVE suportar capacidade offline para interação com o roteiro em áreas sem conectividade e sincronização reativa imediata assim que voltar à área de cobertura.
- **FR-LOG-02 - Monitoramento Contínuo**: Rastreamento de GPS em tempo real DEVE ser exibido ativamente na matriz associando um veículo à uma localização e hora.
- **FR-LOG-03 - Net Price Dinâmico de Rua**: O entregador DEVE ser capaz de editar o peso efetivo da sucata in loco, permitindo que o sistema recalcule dinamicamente o valor do pedido para o cliente com base nas regras do módulo 005.
- **FR-LOG-04 - Pagamento Particionado**: O sistema deve habilitar e armazenar modalidades múltiplas de transação/recebimento no app móvel (Dinheiro e Cartão na mesma entrega, por exemplo).
- **FR-LOG-05 - Trava de Encerramento**: A conversão rigorosa do Vale em 'Faturado' nas dependências de logísticas devem respeitar que os recebíveis batem com a sucata registrada em sistema e os meios físicos de dinheiro e boletos de controle do motorista.

## User Scenarios

### US01: Entrega Offline e Sincronização
**Given** que o entregador parou o caminhão dentro de um subsolo com zero conectividade de rede
**When** ele pesa a sucata, calcula o valor e finaliza todos os "Recebimentos Móveis" 
**Then** os dados ficam preservados no dispositivo do entregador, permitindo com que ele execute a rota seguinte. Em um raio de acesso LTE/4G instantes seguintes, o sistema empurra agressivamente todos os dados gerados para central na base de matriz de forma invisível.

### US02: Recálculo do Net Price Dinâmico C/ Quebra
**Given** que a venda contava preliminarmente com o retorno de duas sucatas de 20KG
**When** o entregador chega ao cliente e uma bateria estava totalmente corroída/danificada e é rejeitada
**Then** ele ajusta pelo aplicativo local que retornará meramente 1 bateria. O aplicativo cobra digitalmente a diferença estipulada pela regra e o Entregador recebe o novo valor exato da mão do cliente.

### US03: Rastreamento Operacional e Visual
**Given** que há mais de 10 Entregadores rodando na filial
**When** a sala de controle abre o mapa tático 
**Then** eles podem acompanhar o avanço dos entregadores simultaneamente, atualizado sem causar impacto de lentidão, permitindo realocar novos pedidos para quem está nas proximidades do novo cliente de emergência.

## Edge Cases
- **Entregador coleta Sucata de Terceiros e Volume Cedo**: Podem haver pesagens massivas de Clientes/Revendas que ultrapassam as previsões, devendo adicionar os quilogramas em excesso na "Conta de Sucata Corrente" do cliente e gerando superávits nas transações de Logística Reversa (via Módulo 004).
- **Falta de PIX Confirmada**: Se o cliente afirma que transferiu mas a API/Dashboard não confirma na rua, o Entregador submete o comprovante visual assinado como flag `Contestável` não barrando a entrega se sua gerência permitir com um override.

## Success Criteria
- **SC-LOG-01**: A aplicação suporta até 1 shift inteiro (+- 8 horas corridas) em operação assíncrona blindada sem deslogar usuários em cachê.
- **SC-LOG-02**: O despachante de frota local tem visibilidade de posições GPS refrescadas num delay `< 10 segundos`.
- **SC-LOG-03**: Índice de confiabilidade da sincronicidade dos registros atinge 100% de estabilidade pós re-handshake online da frota na fábrica.
