# Performance Guide

## Objetivo

Este guia resume pontos de performance relevantes para o ERP Core e para os microservicos com base na arquitetura e nas consultas existentes no codigo.

## Principios Gerais

- prefira Eloquent com `select()` e eager loading consciente
- reduza payloads de sincronizacao e APIs
- empurre trabalho pesado para filas
- garanta indices nas colunas mais filtradas
- monitore consultas sensiveis por tenant

## 1. Banco central e resolucao de tenant

### Pontos sensiveis

- busca por subdominio em `Cliente`
- filtros por `status` no catalogo central
- health checks e listagens operacionais

### O que ja existe

- `subdominio` com `unique`
- indices por `status` nas tabelas centrais novas

### Recomendacoes

- manter lookup por subdominio sempre indexado
- evitar logica de provisionamento no request sincrono
- cachear metadados de tenant quando houver alta frequencia de lookup

## 2. ERP Core e consultas por filial

### Pontos sensiveis

- o projeto aplica escopo tenant-aware por `filial_id`
- listagens grandes em Livewire podem degradar sem paginacao

### Recomendacoes

- sempre paginar grids administrativas e operacionais
- incluir `select()` quando a tela nao precisa da entidade inteira
- revisar indices compostos combinando `filial_id` com colunas de filtro frequente

Exemplos comuns:

- `filial_id, status`
- `filial_id, created_at`
- `filial_id, cliente_id`

## 3. Mobile sync

O endpoint `GET /api/sync/mobile` ja aplica uma boa pratica: usa `select()` e carrega apenas campos necessarios em `Fabricante`, `Veiculo` e `baterias`.

### Recomendacoes

- manter esse endpoint enxuto
- nao incluir colunas pesadas ou relacionamentos desnecessarios
- se o volume crescer, considerar sync incremental por `updated_at`

## 4. Jobs e processamento assincrono

### Quando usar fila

- emissao fiscal
- conciliacao
- notificacoes
- importacoes
- recalculos operacionais

### Recomendacoes

- use Redis para filas
- mantenha workers separados por criticidade quando o volume crescer
- monitore timeout e quantidade de retries
- evite jobs muito grandes; prefira fragmentacao por lote

## 5. Indices importantes ja identificados

No codigo e migrations atuais, ha alguns campos claramente sensiveis:

- `clientes.subdominio`
- `nota_fiscal_jobs.correlation_id`
- `cobrancas.idempotency_key`
- `cobrancas.nosso_numero`
- `cobrancas.txid`
- `cobrancas.status`
- `contato_blacklist.numero_tel`
- `transacao_bancarias.deduplicacao_hash`
- `endereco_geocodificados.endereco_hash`
- `nota_fiscal_orquestradas.idempotency_key`
- `boleto_orquestrados.idempotency_key`
- `fila_contingencias.idempotency_key`

### Recomendacoes

- preserve esses indices em qualquer refactor
- quando adicionar filtros novos de negocio, crie o indice na mesma entrega

## 6. Consultas que merecem atencao

### Financeiro

Existem consultas por:

- `status`
- `valor_total`
- `resultado`
- `bateria_id`

### Logistica

Existem consultas por:

- rota atual
- ordem da parada
- entregador
- ETA e localizacao

### Orquestracao

Existem consultas por:

- `idempotency_key`
- `nosso_numero`
- `correlation_id`
- `status`

### Recomendacoes

- auditar `EXPLAIN ANALYZE` nas consultas mais acessadas
- criar indices compostos alinhados com os `where` reais
- evitar `like '%texto%'` em tabelas muito grandes sem estrategia apropriada

## 7. Eager loading e N+1

O projeto ja usa `with()` em varios pontos importantes.

Exemplos observados:

- `Veiculo::with('baterias')`
- `Rota::with('paradas')`
- `Vale::with(['cliente', 'vendedor', 'itens'])`

### Recomendacoes

- manter `with()` nas telas com relacionamentos
- evitar acessar relacionamento dentro de loops sem eager loading
- quando o relacionamento for muito grande, considere pagina-lo separadamente

## 8. Livewire e grids operacionais

### Boas praticas

- use `paginate()` em listas
- aplique filtros antes de carregar relacionamentos pesados
- limite dashboards de resumo a poucas linhas ou contagens agregadas

### Pontos de atencao

- selects enormes para comboboxes
- listas completas em dashboards sem paginacao
- refreshs excessivos em componentes reativos

## 9. Microservicos

### MS-001 Fiscal

- `correlation_id` precisa continuar unico para idempotencia
- fila de contingencia deve consultar por `nota_id` e status sem full scan

### MS-002 Bancario

- `idempotency_key`, `txid` e `nosso_numero` sao chaves quentes
- webhooks devem sempre consultar por campo indexado

### MS-003 WhatsApp

- blacklist deve ser consultada por `numero_tel`
- historico e fila devem usar ordenacao simples e filtros indexaveis

### MS-004 Open Finance

- deduplicacao por `deduplicacao_hash` evita repeticao de transacoes
- logs de captura tendem a crescer rapido; monitore retenção

### MS-005 Geocoding

- cache por `endereco_hash` reduz chamadas repetidas
- consultas de rota devem carregar `paradas` com eager loading

## 10. Query patterns a evitar

Evite:

- carregar tudo com `get()` em telas administrativas grandes
- `with()` em relacionamentos profundos sem necessidade
- recalculos pesados dentro do request web
- filtros sem indice em tabelas operacionais grandes

Prefira:

- `paginate()`
- `limit()`
- `select()`
- jobs assíncronos
- agregacoes precomputadas quando fizer sentido

## 11. Observabilidade

Para medir gargalos em producao:

- monitore tempo medio de resposta por endpoint
- monitore lag de fila
- monitore consultas lentas do PostgreSQL
- acompanhe crescimento de tabelas de log, fila e webhook

## 12. Checklist rapido antes de otimizar

1. Confirmar endpoint ou tela lenta
2. Medir consulta ou job real
3. Validar indice existente
4. Revisar eager loading
5. Reduzir colunas carregadas
6. Mover trabalho para fila quando apropriado
7. Revalidar com teste de carga ou benchmark simples

## Referencias

- [ARCHITECTURE.md](./ARCHITECTURE.md)
- [MICROSERVICES.md](./MICROSERVICES.md)
- [API_GUIDE.md](./API_GUIDE.md)
- [database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php](./database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php)
- [database/migrations/tenant/2026_04_23_000003_create_tenant_inventory_and_sales_tables.php](./database/migrations/tenant/2026_04_23_000003_create_tenant_inventory_and_sales_tables.php)
