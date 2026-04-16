# Implementation Plan: Módulo de Logística e App do Entregador

**Branch**: `006-logistics-delivery-app`
**Input**: Feature specification from `/specs/006-logistics-delivery-app/spec.md`

## Technical Context
**Primary Dependencies**: Laravel 12, PWA + Service Workers via Vite (Build JS), Livewire 4, Alpine.js (para o frontend dinâmico no App do Motorista)
**Storage**: PostgreSQL 15+, IndexedDB no lado do cliente (Navegador/Celular).

## Project Structure
- **PWA e Service Workers**: Para atingir a métrica de "Até 8 horas logado blindado sem deslogar em Offline" e sincronicidades confiáveis exigidas pelo Escopo (SC-LOG-01), o Front-End da logística será ancorado através do protocolo PWA com um escopo de ServiceWorker registrando requisições Fetch na promessa de rede (Background Sync API acoplado num cache robusto no IndexedDB).
- **WebSocket p/ GPS Real-Time**: Considerando as regras claras de (SC-LOG-02) GPS sem impacto no BD com refresh `< 10 segundos`, criaremos canais de presense de geolocalização operando em um pipeline WebSockets (usando `Laravel Reverb`). Ao invés de pesados `inserts` ao BD no tracking dinâmico, os navegadores apenas recebem por um canal WebSocket. Apenas posições de check-in / check-out dos Pontos de Entrega gravarão no Banco visando escalonamento assíncrono rigoroso.
- **Rest APIs Pós-Venda**: Criação de rotas `/api/v1/logistics/sync` estruturadas em payload JSON para validar dados defasados durante restabelecimento de conexão via ServiceWorker.
