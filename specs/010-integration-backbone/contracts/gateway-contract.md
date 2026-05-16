# Contract: Integration Gateway

## Purpose

Definir a governança mínima para chamadas síncronas controladas entre ERP e serviços externos quando o broker não for o mecanismo adequado.

## Inbound requirements

- autenticação obrigatória
- correlação obrigatória por request
- rate limit por serviço e tenant quando aplicável
- logging estruturado sem exposição de segredos

## Outbound requirements

- endpoint registrado no catálogo do gateway
- timeout explícito
- política de circuit breaker definida
- rastreio de latência e falha

## Minimum metadata

| Field | Required | Description |
|-------|----------|-------------|
| `service_name` | Yes | Nome lógico do destino |
| `route_name` | Yes | Nome interno da integração |
| `method` | Yes | Método HTTP ou equivalente |
| `target_url` | Yes | Destino configurado |
| `correlation_id` | Yes | Identificador de rastreio ponta a ponta |
| `idempotency_key` | Conditional | Obrigatório em operações com efeito de negócio |
| `timeout_ms` | Yes | Limite operacional da chamada |

## Failure handling

- falha transitória deve ser distinguida de falha permanente
- falha síncrona crítica deve registrar entrega operacional
- fallback para fluxo assíncrono deve ser explícito quando existir
