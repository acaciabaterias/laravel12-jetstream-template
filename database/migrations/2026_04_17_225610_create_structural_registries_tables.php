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
        // Fabricantes
        Schema::create('fabricantes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Veículos
        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fabricante_id')->constrained('fabricantes')->onDelete('cascade');
            $table->string('modelo');
            $table->string('motorizacao')->nullable();
            $table->integer('ano_inicio')->nullable();
            $table->integer('ano_fim')->nullable();
            $table->jsonb('atributos_dinamicos')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['fabricante_id', 'modelo']);
        });

        // Baterias
        Schema::create('baterias', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->string('marca');
            $table->string('tecnologia')->nullable(); // AGM, Gel, Chumbo-Ácido
            $table->integer('amperagem')->nullable();
            $table->string('polo')->nullable();
            $table->decimal('preco_venda', 12, 2)->default(0);
            $table->jsonb('atributos_dinamicos')->nullable();

            // Logística Reversa (Princípio IV)
            $table->decimal('peso_sucata_kg', 10, 2)->nullable();
            $table->decimal('valor_base_sucata_kg', 10, 2)->nullable();
            $table->boolean('tem_logistica_reversa')->default(true);

            $table->softDeletes();
            $table->timestamps();

            $table->index('sku');
        });

        // Aplicações (N:N entre Veículo e Bateria)
        Schema::create('aplicacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->onDelete('cascade');
            $table->foreignId('bateria_id')->constrained('baterias')->onDelete('cascade');
            $table->text('observacao')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['veiculo_id', 'bateria_id'], 'idx_unique_aplicacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aplicacoes');
        Schema::dropIfExists('baterias');
        Schema::dropIfExists('veiculos');
        Schema::dropIfExists('fabricantes');
    }
};
