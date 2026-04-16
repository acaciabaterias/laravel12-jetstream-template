# Implementation Plan: Módulo de Vendas e "Vales"

**Branch**: `005-sales-vales`
**Input**: Feature specification from `/specs/005-sales-vales/spec.md`

## Technical Context
**Primary Dependencies**: Laravel 12, Livewire 4, Tailwind CSS 4
**Storage**: PostgreSQL 15+

## Project Structure
- **Global Scope**: Isolamento total de Vales e Pedidos por `filial_id`.
- **Cache Layers**: Cache local/Redis para a tabela de peso/preço da sucata, garantindo a performance `< 500ms` requerida de resposta nas telas.
- **Queues**: Processamento de conversão assíncrono para os Pedidos de Venda/Ordens de Serviço e KARDEX usando job queues via Redis para aliviar o front-end.
- **Database Transactions (ACID)**: Qualquer transição de "Reservado" para "Saída" ou estornos (Cancelamento) do Vale devem rodar dentro de transações rigorosas no PostgreSQL para eliminar divergências de estoque por concorrência.
