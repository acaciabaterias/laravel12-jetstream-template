# Guia do Usuário Final - ERP BateriaExpert

## Visão Geral

O ERP BateriaExpert é um sistema especializado para gestão de revendas de baterias automotivas, com foco em logística reversa de sucata e mobilidade para entregadores.

## Acessando o Sistema

1. Abra o navegador e acesse `https://{seu-dominio}.erp.com`
2. Insira seu email e senha fornecidos pelo administrador
3. Clique em "Entrar"

## Perfis de Usuário e Funcionalidades

### Balconista / Vendedor

- **Vendas**: Criar Vales (pedidos abertos), calcular preço com sucata
- **Clientes**: Cadastrar e consultar clientes
- **Estoque**: Consultar disponibilidade de baterias
- **Orçamentos**: Gerar orçamentos para clientes

### Entregador (App Mobile)

- **Rotas**: Visualizar rota de entregas do dia
- **Entregas**: Registrar peso da sucata coletada
- **Pagamentos**: Registrar recebimentos (Pix, Cartão, Dinheiro)
- **GPS**: Compartilhar localização em tempo real

### Técnico

- **Garantias**: Abrir Ordem de Serviço de garantia
- **Laudos**: Registrar laudo técnico (Procedente/Improcedente)
- **Empréstimos**: Gerenciar baterias de reserva

### Gestor / Dono

- **Dashboard**: Visualizar métricas do negócio
- **Usuários**: Gerenciar colaboradores e permissões
- **Relatórios**: Gerar relatórios de vendas, estoque, garantias
- **Configurações**: Personalizar logo, cores, informações da empresa

### Super Administrador (Dono do SaaS)

- **Tenants**: Gerenciar empresas assinantes
- **Planos**: Configurar planos de assinatura
- **Suporte**: Acessar qualquer tenant para suporte

## Fluxos Principais

### Como vender uma bateria (Balconista)

1. Acesse "Vendas" → "Novo Vale"
2. Selecione o cliente (ou cadastre um novo)
3. Adicione os itens (baterias)
4. Marque se o cliente vai devolver a sucata
   - Se NÃO devolver → preço final sofre acréscimo
   - Se SIM devolver → preço normal
5. Finalize o Vale
6. Emita a Nota Fiscal (se aplicável)

### Como realizar uma entrega (Entregador - App)

1. Abra o app e faça login
2. Acesse "Minha Rota"
3. Para cada entrega:
   - Registre o peso real da sucata coletada
   - Registre o recebimento (Pix, Cartão, Dinheiro)
   - Confirme a entrega
4. Ao final, sincronize os dados (online)

### Como abrir uma garantia (Técnico)

1. Acesse "Garantias" → "Nova OS"
2. Busque o cliente e a venda original
3. Registre o problema e colete fotos
4. Após análise, registre o laudo:
   - **Procedente**: Cliente tem direito à troca
   - **Improcedente**: Cobrar serviço/recarga
5. Gerencie empréstimo de bateria reserva (se necessário)

## Suporte

- **Email**: suporte@bateriaexpert.com
- **WhatsApp**: (11) 99999-9999
- **Horário**: Segunda a Sexta, 8h às 18h
