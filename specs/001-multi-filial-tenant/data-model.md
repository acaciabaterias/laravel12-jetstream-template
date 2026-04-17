# Data Model: Isolated Tenancy Architecture

## Central Database (Platform Metadados)

### Cliente (`clientes`)
Representa um assinante do ERP.
- `id` (UUID, PK)
- `cnpj` (String, Unique)
- `razao_social` (String)
- `nome_fantasia` (String, nullable)
- `email_contato` (String)
- `subdominio` (String, Unique)
- `status` (Enum: active, expired, trial, cancelled)
- `plano` (String: essential, plus, pro)
- `supabase_host` (String, optional)
- `supabase_password` (String, encrypted, optional)
- Timestamps

### Plano de Assinatura (`planos`)
- `id` (Integer, PK)
- `nome` (String)
- `slug` (String, Unique)
- `preco` (Decimal 10,2)
- Timestamps

### Assinatura (`assinaturas`)
- `id` (Integer, PK)
- `cliente_id` (UUID, FK -> clientes)
- `plano_id` (Integer, FK -> planos)
- `status` (String)
- `validade` (Date)
- Timestamps

### Fatura (`faturas`)
- `id` (Integer, PK)
- `assinatura_id` (Integer, FK -> assinaturas)
- `valor` (Decimal)
- `data_vencimento` (Date)
- `status` (String: pending, paid, overdue)
- Timestamps

---

## Tenant Database (Client ERP Instances)

*Note: O Tenant DB não possui tabela de filiais para controle de assinatura. Cada instância pertence a um único Assinante Central.*

### Usuário (`users`)
Usuário do ERP daquele cliente específico.
- `id` (Integer, PK)
- `name` (String)
- `email` (String, Unique)
- `password` (String)
- `role` (String)
- ... (outros campos Jetstream/Fortify)

*Nota: Legado `filial_id` foi excluído do modelo Tenant.*

### White Label Config (`white_label_configs`)
Configurações de branding do ERP para a instância.
- `id` (Integer, PK)
- `titulo_login` (String)
- `cor_primaria` (String)
- `cor_secundaria` (String)
- `logo_url` (String)
- `favicon_url` (String)
- `custom_css` (Text, nullable)
- Timestamps
