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
        Schema::connection($this->connection)->create('depositos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150)->unique();
            $table->string('tipo', 50)->default('principal');
            $table->string('status', 30)->default('ativo');
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('estoque_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bateria_id')->constrained('baterias')->cascadeOnDelete();
            $table->foreignId('deposito_id')->constrained('depositos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo_operacao', 50);
            $table->string('origem', 80)->nullable();
            $table->integer('quantidade');
            $table->text('justificativa')->nullable();
            $table->timestampTz('data_movimentacao');
            $table->timestampsTz();

            $table->index(['deposito_id', 'bateria_id']);
        });

        Schema::connection($this->connection)->create('estoque_saldos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bateria_id')->constrained('baterias')->cascadeOnDelete();
            $table->foreignId('deposito_id')->constrained('depositos')->cascadeOnDelete();
            $table->integer('quantidade_atual')->default(0);
            $table->timestampsTz();
            $table->unique(['bateria_id', 'deposito_id']);
        });

        Schema::connection($this->connection)->create('xml_importacoes', function (Blueprint $table) {
            $table->id();
            $table->string('chave_nfe', 80)->unique();
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->nullOnDelete();
            $table->string('status', 30)->default('pendente');
            $table->text('log_erros')->nullable();
            $table->jsonb('payload_xml')->nullable();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('conta_sucata_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->string('entidade_tipo', 50);
            $table->unsignedBigInteger('entidade_id')->nullable();
            $table->string('tipo_movimento', 30);
            $table->decimal('quantidade_kg', 10, 2);
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('saldo_resultante', 12, 2)->default(0);
            $table->string('origem', 80)->nullable();
            $table->timestampsTz();

            $table->index(['entidade_tipo', 'entidade_id']);
        });

        Schema::connection($this->connection)->create('vales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('vendedor_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('aberto');
            $table->timestampTz('data_criacao');
            $table->timestampTz('data_faturamento')->nullable();
            $table->text('observacoes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestampsTz();

            $table->index(['cliente_id', 'status']);
        });

        Schema::connection($this->connection)->create('itens_vale', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vale_id')->constrained('vales')->cascadeOnDelete();
            $table->foreignId('bateria_id')->constrained('baterias')->cascadeOnDelete();
            $table->integer('quantidade');
            $table->decimal('preco_unitario_original', 12, 2);
            $table->decimal('preco_unitario_final', 12, 2);
            $table->boolean('flag_devolveu_sucata')->default(true);
            $table->text('observacao')->nullable();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('pedidos_venda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vale_id')->constrained('vales')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->timestampTz('data_emissao');
            $table->decimal('valor_total', 12, 2);
            $table->string('status', 30)->default('faturado');
            $table->string('nf_referencia', 150)->nullable();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('ordens_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vale_id')->constrained('vales')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('tecnico_responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('data_abertura');
            $table->string('status', 30)->default('aberta');
            $table->text('laudo')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('reservas_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vale_id')->constrained('vales')->cascadeOnDelete();
            $table->foreignId('item_vale_id')->constrained('itens_vale')->cascadeOnDelete();
            $table->foreignId('bateria_id')->constrained('baterias')->cascadeOnDelete();
            $table->foreignId('deposito_id')->constrained('depositos')->cascadeOnDelete();
            $table->integer('quantidade');
            $table->string('status', 30)->default('reservada');
            $table->timestampsTz();
        });

        DB::connection($this->connection)->statement(
            'alter table estoque_movimentacoes add constraint estoque_movimentacoes_quantidade_check check (quantidade >= 0)'
        );
        DB::connection($this->connection)->statement(
            'alter table estoque_saldos add constraint estoque_saldos_quantidade_atual_check check (quantidade_atual >= 0)'
        );
        DB::connection($this->connection)->statement(
            'alter table itens_vale add constraint itens_vale_quantidade_check check (quantidade > 0)'
        );
        DB::connection($this->connection)->statement(
            'alter table reservas_estoque add constraint reservas_estoque_quantidade_check check (quantidade > 0)'
        );
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('reservas_estoque');
        Schema::connection($this->connection)->dropIfExists('ordens_servico');
        Schema::connection($this->connection)->dropIfExists('pedidos_venda');
        Schema::connection($this->connection)->dropIfExists('itens_vale');
        Schema::connection($this->connection)->dropIfExists('vales');
        Schema::connection($this->connection)->dropIfExists('conta_sucata_movimentacoes');
        Schema::connection($this->connection)->dropIfExists('xml_importacoes');
        Schema::connection($this->connection)->dropIfExists('estoque_saldos');
        Schema::connection($this->connection)->dropIfExists('estoque_movimentacoes');
        Schema::connection($this->connection)->dropIfExists('depositos');
    }
};
