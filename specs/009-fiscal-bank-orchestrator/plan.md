# Implementation Plan: Módulo de Orquestração Fiscal e Bancária

**Branch**: `009-fiscal-bank-orchestrator`
**Input**: Feature specification from `/specs/009-fiscal-bank-orchestrator/spec.md`

## Technical Context
**Primary Dependencies**: Laravel 12 (HTTP Client Facade avançado), Gateways Patterns em PHP (SOLID), e Queues/Horizon robusto para Circuit Breaker.
**Storage**: PostgreSQL 15, S3 Cloud ou FlySystem local acoplado com Symlink garantindo que os PDFs em Base64 sejam armazenados otimizadamente sem sujar binários no Banco Relacional.

## Project Structure
- **Design Pattern Gateway Injetivo**: Não embutiremos o `Http::post(...)` nas Vendas ou Controllers. Estruturaremos todo o diretório `app/Gateways/FiscalGateway/` e `app/Gateways/BankGateway/`. Qualquer mudança radical em chaves de acesso externas à Microserviços serão sanadas editando um arquivo de configuração cego apenas nessas classes isoladamente sem ferir regras de negócio do ERP. 
- **Backoff Exponencial na Fila**: O ERP rodará a lógica rígida e estendida do ecossistema de Filas do PHP via `Exponential Backoffs`. Exemplo prático:
  Retry 1 em 60 segs, Retry 2 em 300 segs, Retry 3 em Meia Hora, impedindo exaustão de requisições inúteis se os WebServices estiverem completamente mortos de infraestrutura paralela.
- **Idempotency Pattern**: Todo Job e Request orquestrado gerará uma Hash combinada `$idVale . "Boleto"` antes de voar. O MS-Bancário não cobrará fatura dupla porque o orquestrador bloqueia as duplas postagens se desativado for.
