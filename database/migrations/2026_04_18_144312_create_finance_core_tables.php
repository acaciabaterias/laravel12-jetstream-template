<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contas_bancarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filial_id')->constrained('filiais')->onDelete('cascade');
            $table->string('banco');
            $table->string('agencia', 10);
            $table->string('conta', 20);
            $table->string('tipo')->default('corrente');
            $table->text('token_api')->nullable();
            $table->string('status')->default('ativo');
            $table->timestamps();
        });

        Schema::create('transacoes_financeiras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_id')->constrained('contas_bancarias')->onDelete('cascade');
            $table->enum('tipo', ['receita', 'despesa']);
            $table->string('categoria'); // venda, fornecedor, imposto, operacional, garantia
            $table->decimal('valor', 12, 2);
            $table->date('data');
            $table->string('status')->default('pendente'); // pendente, pago, conciliado, cancelado
            $table->foreignId('vale_id')->nullable()->constrained('vales')->onDelete('set null');
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->onDelete('set null');
            $table->string('origem')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->index(['data', 'status', 'tipo']);
        });

        Schema::create('fluxo_caixa_projetados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filial_id')->constrained('filiais')->onDelete('cascade');
            $table->date('data_referencia');
            $table->decimal('saldo_inicial', 15, 2);
            $table->decimal('total_receber', 15, 2);
            $table->decimal('total_pagar', 15, 2);
            $table->decimal('saldo_projetado', 15, 2);
            $table->timestamps();

            $table->unique(['filial_id', 'data_referencia']);
        });

        Schema::create('margem_lucro_reais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bateria_id')->constrained('baterias')->onDelete('cascade');
            $table->string('periodo', 7); // YYYY-MM
            $table->decimal('valor_venda_medio', 12, 2);
            $table->decimal('custo_aquisicao_medio', 12, 2);
            $table->decimal('frete_medio', 12, 2);
            $table->decimal('imposto_medio', 12, 2);
            $table->decimal('comissao_media', 12, 2);
            $table->decimal('margem_final', 12, 2);
            $table->timestamps();

            $table->unique(['bateria_id', 'periodo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('margem_lucro_reais');
        Schema::dropIfExists('fluxo_caixa_projetados');
        Schema::dropIfExists('transacoes_financeiras');
        Schema::dropIfExists('contas_bancarias');
    }
};
