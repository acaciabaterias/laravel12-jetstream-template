# Feature Specification: Módulo de Estoque e Logística Reversa

**Feature Branch**: `004-inventory-reverse-logistics`
**Status**: Ready for Implementation
**Dependencies**: 001-multi-filial-tenant, 002-users-permissions-rbac, 003-structural-registries

## Overview
Gestão completa de entradas e saídas de mercadorias físicas (Baterias) e da "Conta Sucata" (logística reversa). Inclui a importação automática de XML de Notas Fiscais para facilitar a entrada de estoque, controle de depósitos/prateleiras e alertas de "Shelf Life" (tempo de prateleira) para evitar que estoques fiquem obsoletos e percam carga.

## Key Entities
### Movimentação de Estoque
- **Atributos Base**: Produto (Bateria), Filial, Quantidade, Tipo (Entrada/Saída), Origem (Nota Fiscal, Ajuste, Devolução), Data.
### Depósitos / Localização
- **Atributos Base**: Nome (ex: Almoxarifado Principal, Loja, Caminhão), Filial, Status.
### XML Import
- **Atributos Base**: Chave NFe, Fornecedor, Status de Importação, Log de Erros.

## Functional Requirements
- **FR01 - Múltiplos Depósitos**: Permitir criar depósitos distintos dentro de uma Filial (ex: Loja vs. Caminhão de Entrega).
- **FR02 - Importação de XML de Fornecedor**: O sistema DEVE permitir upload de XML de NF-e para automatizar a criação de fornecedores (se novos) e adicionar movimentações de entrada de baterias no estoque.
- **FR03 - Auditoria e Rastreio**: Todas as movimentações de estoque DEVEM gravar histórico (quem fez, quando, motivo).
- **FR04 - Monitoramento de Shelf Life**: O sistema DEVE calcular os dias em estoque desde a última carga baseada na entrada. Se passar de X dias (configurável), alertar no dashboard.
- **FR05 - Conta Corrente de Sucata**: O sistema DEVE manter o saldo ("Conta Sucata") não apenas do estoque físico, mas também em formato de crédito/débito para Clientes e Fornecedores.

## User Stories
1. **Given** um Gestor, **When** ele recebe mercadorias fisicamente, **Then** ele pode fazer upload do XML da nota fiscal e o sistema credita as baterias correspondentes ao estoque da sua filial.
2. **Given** um produto parado há 6 meses, **When** o Gestor abre o dashboard de Shelf Life, **Then** o sistema exibe a bateria em aviso indicando risco de perda de carga.
3. **Given** um Estoquista, **When** ele faz um "Ajuste de Estoque" manual, **Then** o sistema exige que ele preencha uma "Justificativa" e grava um Log de Auditoria.

## Edge Cases
- **Baterias não cadastradas no XML**: Se o XML contiver códigos/EANs que ainda não existem no módulo Cadastros (`003`), o sistema deve pausar a importação e solicitar o de/para ou o cadastro rápido.
- **Estoque Negativo**: O sistema NÃO pode permitir ajustes ou saídas que deixem o estoque negativo na filial sob nenhuma hipótese.

## Success Criteria
- **SC01**: Importação de 1 XML mediano (até 50 itens) ocorre em menos de 5 segundos.
- **SC02**: O saldo consolidado em tempo real deve bater estritamente o somatório exato do extrato de movimentações (princípio de event-sourcing para rastreio).
- **SC03**: Movimentações só podem afetar produtos e depósitos atrelados rigidamente à `filial_id` atual do usuário no momento da sessão.
