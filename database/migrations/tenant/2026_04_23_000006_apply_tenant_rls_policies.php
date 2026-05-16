<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        DB::connection($this->connection)->unprepared(
            file_get_contents(database_path('schema/tenant_rls_policies.sql'))
        );
    }

    public function down(): void
    {
        DB::connection($this->connection)->unprepared(<<<'SQL'
        do $$
        declare
            tbl text;
            all_tables text[] := array[
                'users',
                'password_reset_tokens',
                'sessions',
                'permissoes',
                'papel_permissao',
                'audit_logs_acesso',
                'audit_logs',
                'clientes',
                'fornecedores',
                'fabricantes',
                'veiculos',
                'baterias',
                'aplicacoes',
                'depositos',
                'estoque_movimentacoes',
                'estoque_saldos',
                'xml_importacoes',
                'conta_sucata_movimentacoes',
                'vales',
                'itens_vale',
                'pedidos_venda',
                'ordens_servico',
                'reservas_estoque',
                'rotas_entrega',
                'pontos_entrega',
                'recebimentos_moveis',
                'geolocalizacao_eventos',
                'sync_eventos',
                'ordens_servico_garantia',
                'baterias_emprestimo',
                'notificacoes_whatsapp',
                'indices_retorno_produto',
                'contas_bancarias',
                'transacoes_financeiras',
                'fluxos_caixa_projetado',
                'margens_lucro_real',
                'conciliacoes_pendentes',
                'fechamentos_contabeis',
                'notas_fiscais_orquestradas',
                'boletos_orquestrados',
                'filas_contingencia',
                'cnab_remessas',
                'cnab_retorno_uploads'
            ];
        begin
            foreach tbl in array all_tables loop
                execute format('drop policy if exists service_role_all on public.%I', tbl);
                execute format('drop policy if exists authenticated_read on public.%I', tbl);
                execute format('drop policy if exists authenticated_admin_write on public.%I', tbl);
                execute format('drop policy if exists authenticated_stock_write on public.%I', tbl);
                execute format('drop policy if exists authenticated_sales_write on public.%I', tbl);
                execute format('drop policy if exists authenticated_service_write on public.%I', tbl);
                execute format('drop policy if exists authenticated_logistics_write on public.%I', tbl);
                execute format('drop policy if exists authenticated_audit_insert on public.%I', tbl);
                execute format('drop policy if exists authenticated_audit_admin_manage on public.%I', tbl);
                execute format('alter table public.%I disable row level security', tbl);
            end loop;
        end $$;

        drop function if exists app.can_write(text[]);
        drop function if exists app.has_any_role(text[]);
        drop function if exists app.is_active_user();
        drop function if exists app.current_app_role();
        drop function if exists app.current_app_user_id();
        drop function if exists app.jwt_claims();
        SQL);
    }
};
