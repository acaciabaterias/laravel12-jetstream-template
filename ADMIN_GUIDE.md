# Guia do Administrador - ERP BateriaExpert

## Acessos e Permissões

### Gerenciar Usuários

1. Acesse "Configurações" → "Usuários"
2. Clique em "Novo Usuário"
3. Preencha: nome, email, papel (dono, gestor, vendedor, técnico, estoquista, entregador)
4. O usuário receberá email para definir senha

### Papéis e Permissões

| Papel | Permissões |
|-------|-------------|
| Dono | Acesso total ao tenant (exceto criar super admin) |
| Gestor | Acesso total, mas não pode gerenciar assinatura |
| Vendedor | Vendas, clientes, consulta de estoque |
| Técnico | OS de garantia, laudos, empréstimos |
| Estoquista | Estoque, compras, movimentações |
| Entregador | App móvel, rotas, entregas |

## Configurações do Tenant

### White Label (Personalização)

1. Acesse "Configurações" → "Marca"
2. Faça upload do logotipo
3. Escolha as cores primária e secundária
4. Personalize o favicon

### Informações da Empresa

1. Acesse "Configurações" → "Empresa"
2. Preencha: CNPJ, razão social, endereço
3. Configure dados de contato (email, telefone)

## Monitoramento

### Dashboard do Gestor

- Vendas do dia/semana/mês
- Estoque: produtos abaixo do mínimo
- Garantias: OS abertas, índice de retorno
- Financeiro: contas a receber/pagar

### Relatórios

- **Relatório de Vendas**: Exportar por período
- **Relatório de Estoque**: produtos, saldos, giro
- **Relatório de Garantias**: por marca, modelo, índice de retorno
- **Relatório Financeiro**: margem de lucro real

## Manutenção

### Backup

- Backups automáticos diários
- Retenção: 30 dias
- Restauração via comando: `./restore.sh backup_file.sql`

### Logs

- Sistema de logs integrado
- Auditoria de acessos e ações
- Logs de erro disponíveis em `storage/logs/`

## Super Admin (Dono do SaaS)

### Gerenciar Tenants

1. Acesse o dashboard do Super Admin
2. Veja lista de todos os clientes (tenants)
3. Clique em "Criar Tenant" para novo cliente
4. Acesse "Impersonate" para entrar como tenant

### Planos de Assinatura

| Plano | Preço | Usuários | Estoque | White Label |
|-------|-------|----------|---------|-------------|
| Essential | R$147 | 3 | 500 | ❌ |
| Pro | R$297 | 10 | 2.000 | ✅ |
| Enterprise | R$597 | Ilimitado | Ilimitado | ✅ |

### Configurar Planos

1. Acesse "Configurações" → "Planos"
2. Edite preços, limites ou crie novos planos
3. As alterações afetam novos clientes
