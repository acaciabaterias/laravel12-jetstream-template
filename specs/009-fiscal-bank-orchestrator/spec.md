# Feature Specification: Módulo de Orquestração Fiscal e Bancária

**Feature Branch**: `009-fiscal-bank-orchestrator`
**Status**: Ready for Implementation
**Dependencies**: 001-multi-filial-tenant, 002-users-permissions-rbac, 005-sales-vales, 008-intelligent-finance

## Overview
Este módulo atua EXCLUSIVAMENTE como uma camada de **Orquestração (API Gateway interno)**. Ele é responsável por delegar as emissões contábeis (Notas Fiscais) e bancárias (Boletos) para Microserviços (MS) externos e autônomos. Ele **não** calcula impostos, nem emite ou formata boletos, e nem valida malhas de retorno CNAB; apenas gerencia as requisições ativas, filas de contigências vitais em caso de falha externa (Circuit Breaker) e amarra/guarda a linkagem oficial validada e retornada à Fatura do ERP principal.

## Key Entities
- **NotaFiscal**: (id, vale_id, chave_acesso, xml, status, ms_requisicao_id)
- **Boleto**: (id, vale_id, nosso_numero, linha_digitavel, pdf_url, status)
- **FilaContingencia**: (id, tipo_fiscal_bancario, payload, tentativas, proxima_tentativa)

## Functional Requirements
- **FR-ORQ-01 - Orquestração Fiscal (MS-Fiscal)**: O ERP DEVE enviar o payload de Venda Finalizada via Contract Http até o MS-Fiscal para emissões de NF-e/NFC-e. Apenas armazena XML, Chave de Acesso e CC-e consumidos.
- **FR-ORQ-02 - Orquestração Bancária (MS-Bancário)**: O ERP DEVE solicitar ao MS-Bancário a criação de Boletos/Pagamentos engessando requisições, resgatando e apenas expondo aos vendedores os resultados estáticos gerados lá (linha digitável, QrCode PIX ou Link).
- **FR-ORQ-03 - Fila de Contingência Ativa (Retry Engine)**: Se qualquer um dos MSs paralelos ou SEFAZ falharem respondendo 5xx, o fluxo deve interromper falhas críticas jogando a transação crua em uma `FilaContingencia`, retentando cronologicamente depois sem travar o vendedor no momento.
- **FR-ORQ-04 - Upload e Remessa CNAB Passiva**: O sistema DEVE prover painel simples listando envios (REM) para download gerados pelo MS e permitir o Upload File dos (.RET) CNAB pelo Usuário. O ERP engole o buffer do RET e espelha atirando fielmente o pacote mastigado para processamento solitário de validação no MS Bancário.

## User Scenarios

### US01: Emissão Imediata Transparente
**Given** que o Vendedor clicou em "Finalizar Faturamento e Imprimir" após fechar a Venda
**When** a Engine orquestradora despacha o JSON e chama o gateway
**Then** o MS-Fiscal e MS-Bancário validam tudo em menos de 1 segundo. A Danfe e o Comprovante brotam no Painel em tempo real, guardando o XML retornado nas tabelas estáticas da Filial.

### US02: Contigência de Interrupção SEFAZ/Servidor
**Given** que faturamentos aglomeram em final de turno e os servidores base da Sefaz caem
**When** o ERP arremessa o Request e estoura timeout HTTP 504 no Backend Oculto
**Then** o Orquestrador exibe pro vendedor pacatamente *"Pedido Validado Local - Aguardando Emissão Externa SEFAZ na Fila"*. 45 minutos depois, o serviço do MS-Fiscal restabelece e a fila oculta do ERP esvazia todas as pendências ativando os PDFs finais na listagem.

### US03: Importação Cega CNAB
**Given** que uma Analista emite lote de arquivos CNAB do Banco e faz up na interface
**When** ela submete e aciona Iniciar
**Then** como orquestrador, esse módulo engloba todo o buffer Base64 ou Texto massivo subido e impulsiona pra o MS de Boletos validar as quitações, rebaixando a tela à espera do log Final.

## Edge Cases
- **Limite de Timeout Absoluto de Tentativas**: Se uma nota ficou trancada e repetida por 10x dentro da `FilaContingencia` e alcançou limite máximo de estagnação predefinido (> 24h sem comunicação base), ela desativa das retentativas engatilhando SMS/Warning Crítico para T.I/Suporte.
- **Falibilidade e Faturas Duplicadas (Idempotency)**: A segurança obriga que a emissão de boletos via MS utilize Chaves Imutáveis únicas de Sessão Identificadora (UUID). Repetições tardias não causarão boletos acidentais duplicados.

## Success Criteria
- **SC-ORQ-01**: Aderência de Contingências em interações de APIs que resultem inoperantes deve atingir escopo `zero-locked`, operando a base transacional impavidamente (Salva Offline).
- **SC-ORQ-02**: Rigor impeditivo arquitetônico para que ZERO LÓGICA CONTA/TRIBUTO (CFOP/NCM Math) cruzem o limite sintático das classes Gateways deste Módulo. 
- **SC-ORQ-03**: Os tempos-finais para emissões diretas (SEFAZ ok) retornarem o binário base devem demorar `< 1.2 segundos`, mantendo percepção Real-Time pro cliente na portaria.
