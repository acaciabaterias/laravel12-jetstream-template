<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        DB::connection($this->connection)->statement('create extension if not exists pgcrypto');

        Schema::connection($this->connection)->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 150)->unique();
            $table->timestampTz('email_verified_at')->nullable();
            $table->text('password');
            $table->string('papel', 30)->default('vendedor');
            $table->boolean('ativo')->default(true);
            $table->timestampTz('ultimo_login')->nullable();
            $table->ipAddress('ultimo_ip')->nullable();
            $table->rememberToken();
            $table->text('profile_photo_path')->nullable();
            $table->timestampsTz();

            $table->index('papel');
            $table->index('ativo');
        });

        Schema::connection($this->connection)->create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 150)->primary();
            $table->text('token');
            $table->timestampTz('created_at')->nullable();
        });

        Schema::connection($this->connection)->create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity');

            $table->index('user_id');
            $table->index('last_activity');
        });

        Schema::connection($this->connection)->create('permissoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('slug', 100)->unique();
            $table->text('descricao')->nullable();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('papel_permissao', function (Blueprint $table) {
            $table->string('papel', 30);
            $table->foreignId('permissao_id')->constrained('permissoes')->cascadeOnDelete();
            $table->timestampTz('created_at')->useCurrent();

            $table->primary(['papel', 'permissao_id']);
        });

        Schema::connection($this->connection)->create('audit_logs_acesso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->ipAddress('ip');
            $table->text('user_agent')->nullable();
            $table->boolean('sucesso');
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::connection($this->connection)->create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            $table->string('table_name', 100);
            $table->unsignedBigInteger('record_id');
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['table_name', 'record_id']);
        });

        Schema::connection($this->connection)->create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('tipo_pessoa', 20)->default('fisica');
            $table->string('documento', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telefone', 30)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('cep', 12)->nullable();
            $table->string('endereco', 255)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento', 120)->nullable();
            $table->string('bairro', 120)->nullable();
            $table->string('cidade', 120)->nullable();
            $table->string('uf', 2)->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('documento', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telefone', 30)->nullable();
            $table->string('contato_nome', 120)->nullable();
            $table->string('cep', 12)->nullable();
            $table->string('endereco', 255)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento', 120)->nullable();
            $table->string('bairro', 120)->nullable();
            $table->string('cidade', 120)->nullable();
            $table->string('uf', 2)->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestampsTz();
        });

        DB::connection($this->connection)->statement(
            "alter table users add constraint users_papel_check check (papel in ('dono', 'gestor', 'vendedor', 'tecnico', 'estoquista', 'entregador'))"
        );
        DB::connection($this->connection)->statement(
            "alter table papel_permissao add constraint papel_permissao_papel_check check (papel in ('dono', 'gestor', 'vendedor', 'tecnico', 'estoquista', 'entregador'))"
        );
        DB::connection($this->connection)->statement(
            "alter table clientes add constraint tenant_clientes_tipo_pessoa_check check (tipo_pessoa in ('fisica', 'juridica'))"
        );
        DB::connection($this->connection)->statement(
            'create unique index if not exists idx_tenant_clientes_documento_unique on clientes(documento) where documento is not null'
        );
        DB::connection($this->connection)->statement(
            'create unique index if not exists idx_tenant_fornecedores_documento_unique on fornecedores(documento) where documento is not null'
        );
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('fornecedores');
        Schema::connection($this->connection)->dropIfExists('clientes');
        Schema::connection($this->connection)->dropIfExists('audit_logs');
        Schema::connection($this->connection)->dropIfExists('audit_logs_acesso');
        Schema::connection($this->connection)->dropIfExists('papel_permissao');
        Schema::connection($this->connection)->dropIfExists('permissoes');
        Schema::connection($this->connection)->dropIfExists('sessions');
        Schema::connection($this->connection)->dropIfExists('password_reset_tokens');
        Schema::connection($this->connection)->dropIfExists('users');
    }
};
