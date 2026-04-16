# Tasks: Módulo de Orquestração Fiscal e Bancária

**Feature Branch**: `009-fiscal-bank-orchestrator`
**Spec File**: [spec.md](spec.md)

## Phase 1: Storage and Migration Structures
- [ ] T001: Criar migration relacional atrelada para `notas_fiscais` (chave, status da recepcao ms_id, xml string).
- [ ] T002: Criar migration `boletos` espelhando meta dados vindas de bancos (nosso_numero, barcode info, url em pdf).
- [ ] T003: Criar Base de Tabela local separada `filas_contingencias` para fins de controle persistente humano da Retenção além do REDIS em Memória.
- [ ] T004: Adicionar todos Models interrelacionados nas filiais locais que consumiram via `GlobalScope`.

## Phase 2: Design Pattern Application (Gateways)
- [ ] T005: Esboçar e definir interfaces rígidas (Service Contracts) em `/app/Contracts/FiscalGatewayInterface.php` amarrando metódos imutáveis tipo `->emitir($model)`.
- [ ] T006: Codificar adaptação direta na Classe Concreta com pacote utilitário providenciado via `Guzzle` ou `Illuminate\Support\Facades\Http` lidando estritamente com os microserviços.
- [ ] T007: Estabelecer Middleware `UUID Headers` ou Hashes nativas no Pipeline de HTTP Client injetando as blindagens de Idempotência.

## Phase 3: Retry Pattern & Dashboard Alerts
- [ ] T008: Construção do Monitor Livewire de Painel (Estilo Laravel Pulse custom) exibindo o número flutuante de `Contingências Fiscais em Reprocessamento` rodando em Batch Oculto.
- [ ] T009: Tratar Exceptions `ServerException Timeout` dos Gateways puxados nos Handlers para redirecionarem as transações automaticamente para fila Exponencial Backoff de Jobs.
- [ ] T010: Criar o Comando Console que espelha reinjeções da Fila de Tabela Relacional provendo segurança em quedas de Swap / Redis Container falhos.

## Phase 4: Sincronia de Extratos Fiscais 
- [ ] T011: Configurar rotinas Passivas: Área da Contabilidade para Download Lote de Remessas Múltiplas e o Upload Massivo visual do arquivo CNAB.
- [ ] T012: Inserir a transação engolidora onde Laravel bufferiza o arquivo e empurra via Multipart API para MS tratar.

## Phase 5: Testes de Integração Fake & E2E Mocks
- [ ] T013: Testar HTTP Fakes em Suite Interceptando a requisição e retornando Delay infinito/504 confirmando Backoff acionado do Retry.
- [ ] T014: Processar Suite de testes varrendo as Entidades provando Zero Acoplamento (`Sem Arquiteturas Vazadas e de Alta dependência Fiscal Calculativa no Orquestrador`) garantindo SC-ORQ-02 limpo.
