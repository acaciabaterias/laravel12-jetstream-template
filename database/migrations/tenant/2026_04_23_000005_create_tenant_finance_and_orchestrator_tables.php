<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection($this->connection)->create('contas_bancarias', function (Blueprint $table) {
            $table->id();
            $table->string('banco', 120);
            $table->string('agencia', 30);
            $table->string('conta', 40);
            $table->string('tipo', 30);
            $table->text('token_api')->nullable();
            $table->string('status', 30)->default('ativa');
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('transacoes_financeiras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_bancaria_id')->constrained('contas_bancarias')->cascadeOnDelete();
            $table->string('tipo', 30);
            $table->decimal('valor', 12, 2);
            $table->timestampTz('data_transacao');
            $table->boolean('status_conciliado')->default(false);
            $table->string('origem_tipo', 50)->nullable();
            $table->unsignedBigInteger('origem_id')->nullable();
            $table->string('descricao', 255)->nullable();
            $table->string('identificador_externo', 150)->unique();
            $table->timestampsTz();

            $table->index(['origem_tipo', 'origem_id']);
        });

        Schema::connection($this->connection)->create('fluxos_caixa_projetado', function (Blueprint $table) {
            $table->id();
            $table->date('data_referencia');
            $table->decimal('saldo_inicial', 12, 2)->default(0);
            $table->decimal('total_receber', 12, 2)->default(0);
            $table->decimal('total_pagar', 12, 2)->default(0);
            $table->decimal('saldo_projetado', 12, 2)->default(0);
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('margens_lucro_real', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bateria_id')->constrained('baterias')->cascadeOnDelete();
            $table->date('periodo_inicio');
            $table->date('periodo_fim');
            $table->decimal('valor_venda', 12, 2)->default(0);
            $table->decimal('custo_aquisicao', 12, 2)->default(0);
            $table->decimal('frete', 12, 2)->default(0);
            $table->decimal('imposto', 12, 2)->default(0);
            $table->decimal('comissao', 12, 2)->default(0);
            $table->decimal('margem_calculada', 8, 4)->default(0);
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('conciliacoes_pendentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transacao_financeira_id')->constrained('transacoes_financeiras')->cascadeOnDelete();
            $table->string('motivo', 255);
            $table->jsonb('payload_bancario')->nullable();
            $table->string('status', 30)->default('pendente');
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('fechamentos_contabeis', function (Blueprint $table) {
            $table->id();
            $table->string('competencia', 20)->unique();
            $table->string('status', 30)->default('aberto');
            $table->timestampTz('fechado_em')->nullable();
            $table->foreignId('fechado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('notas_fiscais_orquestradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vale_id')->constrained('vales')->cascadeOnDelete();
            $table->string('chave_acesso', 80)->nullable();
            $table->text('xml_path')->nullable();
            $table->string('status', 30)->default('pendente');
            $table->string('ms_requisicao_id', 150)->nullable();
            $table->string('idempotency_key', 150)->unique();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('boletos_orquestrados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vale_id')->constrained('vales')->cascadeOnDelete();
            $table->string('nosso_numero', 80)->nullable();
            $table->string('linha_digitavel', 255)->nullable();
            $table->text('pdf_url')->nullable();
            $table->string('status', 30)->default('pendente');
            $table->string('identificador_externo', 150)->nullable();
            $table->string('idempotency_key', 150)->unique();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('filas_contingencia', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_integracao', 50);
            $table->jsonb('payload');
            $table->integer('tentativas')->default(0);
            $table->timestampTz('proxima_tentativa')->nullable();
            $table->string('status', 30)->default('pendente');
            $table->text('ultimo_erro')->nullable();
            $table->string('idempotency_key', 150)->unique();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('cnab_remessas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_arquivo', 30);
            $table->string('nome_arquivo', 255);
            $table->string('status', 30)->default('gerada');
            $table->text('arquivo_path')->nullable();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('cnab_retorno_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cnab_remessa_id')->nullable()->constrained('cnab_remessas')->nullOnDelete();
            $table->string('nome_arquivo', 255);
            $table->string('status_processamento', 30)->default('pendente');
            $table->text('log_processamento')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('cnab_retorno_uploads');
        Schema::connection($this->connection)->dropIfExists('cnab_remessas');
        Schema::connection($this->connection)->dropIfExists('filas_contingencia');
        Schema::connection($this->connection)->dropIfExists('boletos_orquestrados');
        Schema::connection($this->connection)->dropIfExists('notas_fiscais_orquestradas');
        Schema::connection($this->connection)->dropIfExists('fechamentos_contabeis');
        Schema::connection($this->connection)->dropIfExists('conciliacoes_pendentes');
        Schema::connection($this->connection)->dropIfExists('margens_lucro_real');
        Schema::connection($this->connection)->dropIfExists('fluxos_caixa_projetado');
        Schema::connection($this->connection)->dropIfExists('transacoes_financeiras');
        Schema::connection($this->connection)->dropIfExists('contas_bancarias');
    }
};
