# Implementation Plan: Módulo de Garantias e Feedback

**Branch**: `007-guarantees-feedback`
**Input**: Feature specification from `/specs/007-guarantees-feedback/spec.md`

## Technical Context
**Primary Dependencies**: Laravel 12, PWA + Livewire 4, DomPDF / Browsershot (p/ Geração do PDF Termo), API WhatsApp Cloud / EvolutionAPI (A definir dependendo da contratação do cliente).
**Storage**: PostgreSQL 15+, Discos Storage S3/Local para guarda de Pdfs e Imagens do Laudo.

## Project Structure
- **Worker Padrão Assíncrono para Mensageria**: Para obedecer a métrica `SC-GAR-02` que blinda o usuário de esperar a API do Zap retornar status de conectividade, configuraremos as Notificações encapsuladas em classes do sistema fluindo estritamente via `Queues / Redis` (usando `SendWhatsAppNotificationJob`). Caso uma trigger de envio demore (timeout da API), a Queue processará retries silenciosos em Background.
- **Cache de Desempenho no Cadastro de Lançamento (Índice de Retorno)**: Ponto de gargalo massivo em ERPs (SC-GAR-03) será evitado configurando um Model Observer em `OrdemServicoGarantia`. Ao aprovar ou reprovar OS, o Observer engatilha incremento em uma coluna estática `indice_proporcao` na própria tabela das `Baterias` atreladas, impedindo Selects / Subqueries gigantes na visualização e busca cruciais de rotina.
- **Templating do Termo**: Confecção crua de uma view no diretório `/resources/views/pdf/` processando um template rígido de Documento, convertida à força pela Engine HTML-to-PDF selecionada do ecossistema Laravel.
