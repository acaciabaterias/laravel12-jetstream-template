# Feature Specification: Módulo de Cadastros

**Feature Branch**: `004-structural-registries-v2`  
**Created**: 13 de abril de 2026  
**Status**: Ready for Implementation  
**Input**: Alinhado à Master Specification do ERP de Gestão de Baterias & Logística.

## Multi-Filial (Tenant)

### Gestão de Duplicidade entre Filiais
- Cada filial opera como um "mini-ERP" independente.
- Registros são únicos dentro da mesma filial, mas podem existir registros com o mesmo nome/código em filiais diferentes.

### Produtos (Baterias)
- Duplicidade permitida entre filiais diferentes.
- Mesmo SKU em filiais diferentes é permitido.
- Estoque não é compartilhado entre filiais.
- Preços podem variar entre filiais devido a frete e impostos regionais.

---

## Key Entities *(updated)*

### Veículo (Aplicação)
- **Atributos Base**: Fabricante (Marca), Modelo, Ano Início/Fim, Motorização.
- **Relacionamento / Dinamismo**: Possui uma Lista de Aplicações (N para N com Baterias).
- **Atributos Dinâmicos**: Configurados por categoria (Carro, Moto, Caminhão) com validações customizadas.

### Bateria
- **Atributos Base**: SKU, Marca, Atributos Técnicos (ex: Amperagem, Polo, Tecnologia).
- **Relacionamento / Dinamismo**: Pode estar vinculada a múltiplos Veículos.
- **Atributos Dinâmicos**: Configurados por tipo de bateria (AGM, Chumbo-ácido, Gel) com validações específicas.

### Aplicação
- **Atributos Base**: ID_Veiculo, ID_Bateria, Observação Técnica.
- **Relacionamento / Dinamismo**: Entidade que une o produto ao uso específico.

### Fabricante
- **Atributos Base**: Nome, Código Interno.
- **Relacionamento / Dinamismo**: Associado a Veículos.

---

## Functional Requirements *(updated)*

- **FR07**: O cadastro de veículos deve permitir a seleção de múltiplos produtos compatíveis (Relação Muitos-para-Muitos).
- **FR09 - Busca de Veículos**
  - **Desktop**: Filtros combinados encadeados (Fabricante → Modelo → Ano) com debounce.
  - **Mobile**: Busca textual + filtro rápido por fabricante, com suporte offline (cache local).
  - **Performance**: Resultados em < 500ms para buscas simples e < 2s para buscas complexas.
- **FR09 - Busca de Veículos (Mobile)**

### Mobile (app entregador)
- Busca funciona OFFLINE com cache local
- Sincroniza fabricantes/modelos na inicialização
- Busca textual com autocomplete

### Tarefas para FR09 Mobile
- [ ] T095: Implementar cache local de fabricantes/modelos (IndexedDB)
- [ ] T096: Criar endpoint de sincronização para dados mobile
- [ ] T097: Implementar busca offline com fallback
- [ ] T098: Testar busca sem conexão com internet
- **FR12**: Implementar suporte a multi-filial para todos os cadastros estruturais.
  - **Gestão de Duplicidade entre Filiais**:
    - Cada filial opera como um "mini-ERP" independente.
    - Registros são únicos dentro da mesma filial, mas podem existir registros com o mesmo nome/código em filiais diferentes.
    - Produtos (Baterias):
      - Duplicidade permitida entre filiais diferentes.
      - Mesmo SKU em filiais diferentes é permitido.
      - Estoque não é compartilhado entre filiais.
      - Preços podem variar entre filiais devido a frete e impostos regionais.
- **FR13 - Atributos Dinâmicos**
  - **Gestão de Atributos Dinâmicos**:
    - Apenas usuários com papel "Dono" ou "Gestor" podem gerenciar atributos dinâmicos.
    - "Vendedores" e "Técnicos" apenas visualizam os atributos (não editam).
    - O acesso é via dashboard administrativo após login normal.
  - **Configuração de Atributos Dinâmicos**:
    - Para Baterias:
      - Campos obrigatórios vs opcionais por tipo de bateria (AGM, Chumbo-ácido, Gel).
      - Valores possíveis (ex: Tecnologia: ["AGM", "EFB", "Chumbo-Ácido", "Gel"]).
      - Unidades de medida (ex: Amperagem em Ah, CCA em A).
    - Para Veículos:
      - Campos por categoria (Carro, Moto, Caminhão).
      - Validações customizadas.

- **FR14**: Implementar exclusão lógica (Soft Delete) com status para preservar histórico.
- **FR15 - Logging de Auditoria**
  - **Gestão do Log de Auditoria**:
    - **Acesso**:
      - Dono: Acesso total (todas filiais, todos módulos).
      - Gestor: Acesso total (apenas da sua filial).
      - Vendedor/Técnico/Estoquista: Nenhum acesso.
    - **Interface**:
      - Caminho: Dashboard → Módulo "Auditoria" → "Logs do Sistema".
      - URL: `/admin/audit-logs`.
      - Tela principal com filtros (Data, Usuário, Ação, Tabela, Buscar).
      - Registros exibem: Data, Usuário, Ação, Detalhes (antes/depois), IP, Filial.
    - **Funcionalidades**:
      - Linha do tempo detalhada por registro.
      - Reverter alterações (apenas Dono/Gestor).
      - Exportar logs (CSV, Excel, PDF; máximo 10.000 registros).
    - **Notificações**:
      - Alertas para alterações críticas (ex.: preço > 20%, exclusões com estoque).
    - **Performance**:
      - Carregar lista inicial: 2 segundos.
      - Buscar com filtros: 1 segundo.
      - Exportar 10.000 registros: 10 segundos.
- **FR16**: Implementar suporte a operações em lote com metas de desempenho e escalabilidade.
  - **Definição de Operações em Lote**:
    - Importação de produtos (CSV com novas baterias).
    - Vinculação em massa (aplicar mesma bateria a vários veículos).
    - Atualização de preços (reajuste por tabela de fornecedor).
    - Arquivo em massa (mover produtos para "Inativo").
    - Exportação de dados (relatório de aplicações).
    - Clonagem de aplicações (copiar compatibilidades entre veículos).
  - **Metas de Desempenho**:
    - Importar 1.000 produtos em até 30 segundos.
    - Exportar 50.000 linhas em até 30 segundos.
    - Suporte a 5 operações simultâneas sem degradação perceptível.
  - **Estratégia de Implementação**:
    - Lotes pequenos (até 100 registros): Processamento síncrono com barra de progresso.
    - Lotes médios (100 - 1.000 registros): Processamento em fila com notificações.
    - Lotes grandes (> 1.000 registros): Job queue obrigatório, processamento em chunks.
  - **Tratamento de Erros**:
    - Linhas inválidas são puladas.
    - Produtos duplicados no mesmo lote usam a última ocorrência.
    - Falhas de conexão pausam o job e tentam novamente.
  - **Monitoramento e Alertas**:
    - Dashboard com status de operações em lote.
    - Alertas automáticos para operações lentas ou com falhas.

- **FR08**: O sistema deve permitir a clonagem de aplicações entre veículos do mesmo fabricante ou plataforma similar.
- **FR10**: O sistema deve exibir todos os veículos compatíveis com uma bateria (busca reversa na tela da bateria).
- **FR11**: O sistema deve manter integridade referencial entre todas as entidades (chaves estrangeiras com cascade adequado).

## Success Criteria *(updated)*

- **SC06**: Garantir que registros arquivados não sejam exibidos em operações ativas, mas estejam disponíveis para consulta histórica.
- **SC07**: Permitir a configuração de atributos dinâmicos sem necessidade de deploy adicional.
- **SC08**: Garantir rastreabilidade de todas as operações de CRUD via Log de Auditoria.
  - 100% das ações (criar, alterar, arquivar) são logadas.
  - Usuário sem permissão nunca acessa logs (testado).
  - Buscar histórico de um registro leva < 2 segundos.
  - Exportar logs leva < 3 cliques.
- **SC09**: Operações em lote atendem às metas de desempenho e escalabilidade definidas.

## Logística Reversa (Princípio IV)

### Campos adicionais na tabela baterias
- peso_sucata_kg (decimal, 10,2) - Peso estimado da sucata
- valor_base_sucata_kg (decimal, 10,2) - Valor base por kg
- tem_logistica_reversa (boolean, default true)

### Regras de Negócio
- O estoque de sucata é incrementado APENAS após descarga física na filial.
- Cada filial mantém saldo próprio de sucata (estoque_sucata).
- O cálculo de Net Price usa o valor_base_sucata_kg do momento da venda (trava preço).

---
