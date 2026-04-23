<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        DB::connection($this->connection)->statement('create extension if not exists pgcrypto');

        Schema::connection($this->connection)->create('planos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 80);
            $table->string('slug', 80)->unique();
            $table->decimal('preco_mensal', 12, 2)->default(0);
            $table->integer('max_usuarios')->default(3);
            $table->integer('max_estoque_itens')->default(500);
            $table->boolean('has_white_label')->default(false);
            $table->boolean('has_support_priority')->default(false);
            $table->boolean('ativo')->default(true);
            $table->jsonb('recursos')->default(DB::raw("'{}'::jsonb"));
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('cnpj', 18)->unique();
            $table->string('razao_social', 150);
            $table->string('nome_fantasia', 100)->nullable();
            $table->string('email_contato', 150);
            $table->string('telefone', 30)->nullable();
            $table->string('subdominio', 80)->unique();
            $table->string('status', 30)->default('trial');
            $table->date('trial_ends_at')->nullable();
            $table->date('subscription_ends_at')->nullable();
            $table->foreignId('plano_atual_id')->nullable()->constrained('planos')->nullOnDelete();
            $table->string('supabase_project_ref', 100)->nullable()->unique();
            $table->text('supabase_url')->nullable();
            $table->text('supabase_db_host')->nullable();
            $table->text('supabase_db_password')->nullable();
            $table->text('supabase_anon_key')->nullable();
            $table->text('supabase_service_role_key')->nullable();
            $table->string('provisioning_status', 30)->default('pending');
            $table->boolean('billing_blocked')->default(false);
            $table->jsonb('metadata')->default(DB::raw("'{}'::jsonb"));
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('status');
            $table->index('plano_atual_id');
        });

        Schema::connection($this->connection)->create('white_label_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->unique()->constrained('clientes')->cascadeOnDelete();
            $table->text('logo_url')->nullable();
            $table->text('favicon_url')->nullable();
            $table->string('cor_primaria', 7)->default('#3b82f6');
            $table->string('cor_secundaria', 7)->default('#10b981');
            $table->string('cor_fundo', 7)->default('#f9fafb');
            $table->string('titulo_login', 150)->nullable();
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();
            $table->string('template_nome', 80)->default('default');
            $table->boolean('mostrar_marca_plataforma')->default(true);
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('usuarios_plataforma', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 150)->unique();
            $table->text('password');
            $table->string('papel', 30)->default('support');
            $table->boolean('ativo')->default(true);
            $table->timestampTz('ultimo_login')->nullable();
            $table->ipAddress('ultimo_ip')->nullable();
            $table->timestampsTz();

            $table->index('papel');
        });

        DB::connection($this->connection)->statement(
            "alter table clientes add constraint clientes_status_check check (status in ('trial', 'active', 'expired', 'cancelled', 'suspended', 'provisioning'))"
        );
        DB::connection($this->connection)->statement(
            "alter table clientes add constraint clientes_provisioning_status_check check (provisioning_status in ('pending', 'provisioning', 'ready', 'failed'))"
        );
        DB::connection($this->connection)->statement(
            "alter table usuarios_plataforma add constraint usuarios_plataforma_papel_check check (papel in ('super_admin', 'support', 'billing'))"
        );
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('usuarios_plataforma');
        Schema::connection($this->connection)->dropIfExists('white_label_configs');
        Schema::connection($this->connection)->dropIfExists('clientes');
        Schema::connection($this->connection)->dropIfExists('planos');
    }
};
