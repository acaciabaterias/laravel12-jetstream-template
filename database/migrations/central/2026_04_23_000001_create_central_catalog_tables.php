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
        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement('create extension if not exists pgcrypto');
        }

        if (! Schema::connection($this->connection)->hasTable('planos')) {
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
                $this->addJsonWithEmptyObjectDefault($table, 'recursos');
                $table->timestampsTz();
            });
        } elseif (! Schema::connection($this->connection)->hasColumn('planos', 'recursos')) {
            Schema::connection($this->connection)->table('planos', function (Blueprint $table) {
                $this->addJsonWithEmptyObjectDefault($table, 'recursos');
            });
        }

        if (! Schema::connection($this->connection)->hasTable('clientes')) {
            Schema::connection($this->connection)->create('clientes', function (Blueprint $table) {
                $table->id();
                $table->string('cnpj', 18)->unique();
                $table->string('razao_social', 150);
                $table->string('nome_fantasia', 100)->nullable();
                $table->string('email_contato', 150);
                $table->string('telefone', 30)->nullable();
                $table->string('endereco', 255)->nullable();
                $table->decimal('saldo_sucata_kg', 10, 2)->default(0);
                $table->string('subdominio', 80)->unique();
                $table->string('plano', 80)->default('essential');
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
                $this->addJsonWithEmptyObjectDefault($table, 'metadata');
                $table->timestampsTz();
                $table->softDeletesTz();

                $table->index('status');
                $table->index('plano_atual_id');
            });
        } else {
            Schema::connection($this->connection)->table('clientes', function (Blueprint $table) {
                if (! Schema::connection($this->connection)->hasColumn('clientes', 'plano_atual_id')) {
                    $table->unsignedBigInteger('plano_atual_id')->nullable();
                }

                if (! Schema::connection($this->connection)->hasColumn('clientes', 'subscription_ends_at')) {
                    $table->date('subscription_ends_at')->nullable();
                }

                if (! Schema::connection($this->connection)->hasColumn('clientes', 'billing_blocked')) {
                    $table->boolean('billing_blocked')->default(false);
                }

                if (! Schema::connection($this->connection)->hasColumn('clientes', 'provisioning_status')) {
                    $table->string('provisioning_status', 30)->default('pending');
                }
            });

            if ($this->usesPostgres()) {
                DB::connection($this->connection)->statement('alter table clientes alter column plano type varchar(80)');
                DB::connection($this->connection)->statement('alter table clientes alter column supabase_url type text');
                DB::connection($this->connection)->statement('alter table clientes alter column supabase_db_host type text');
                DB::connection($this->connection)->statement('alter table clientes alter column supabase_db_password type text');
                DB::connection($this->connection)->statement('alter table clientes alter column supabase_anon_key type text');
                DB::connection($this->connection)->statement('alter table clientes alter column supabase_service_role_key type text');
            }
        }

        if (! Schema::connection($this->connection)->hasTable('white_label_configs')) {
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
        }

        if (! Schema::connection($this->connection)->hasTable('usuarios_plataforma')) {
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
        }

        if ($this->usesPostgres()) {
            if (Schema::connection($this->connection)->hasColumn('clientes', 'status')) {
                DB::connection($this->connection)->statement('alter table clientes drop constraint if exists clientes_status_check');
                DB::connection($this->connection)->statement(
                    "alter table clientes add constraint clientes_status_check check (status in ('trial', 'active', 'expired', 'cancelled', 'suspended', 'provisioning'))"
                );
            }

            if (Schema::connection($this->connection)->hasColumn('clientes', 'provisioning_status')) {
                DB::connection($this->connection)->statement('alter table clientes drop constraint if exists clientes_provisioning_status_check');
                DB::connection($this->connection)->statement(
                    "alter table clientes add constraint clientes_provisioning_status_check check (provisioning_status in ('pending', 'provisioning', 'ready', 'failed'))"
                );
            }

            if (Schema::connection($this->connection)->hasColumn('usuarios_plataforma', 'papel')) {
                DB::connection($this->connection)->statement('alter table usuarios_plataforma drop constraint if exists usuarios_plataforma_papel_check');
                DB::connection($this->connection)->statement(
                    "alter table usuarios_plataforma add constraint usuarios_plataforma_papel_check check (papel in ('super_admin', 'support', 'billing'))"
                );
            }
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('usuarios_plataforma');
        Schema::connection($this->connection)->dropIfExists('white_label_configs');
        Schema::connection($this->connection)->dropIfExists('clientes');
        Schema::connection($this->connection)->dropIfExists('planos');
    }

    private function addJsonWithEmptyObjectDefault(Blueprint $table, string $column): void
    {
        if ($this->usesPostgres()) {
            $table->jsonb($column)->default(DB::raw("'{}'::jsonb"));

            return;
        }

        $table->json($column)->nullable();
    }

    private function usesPostgres(): bool
    {
        return DB::connection($this->connection)->getDriverName() === 'pgsql';
    }
};
