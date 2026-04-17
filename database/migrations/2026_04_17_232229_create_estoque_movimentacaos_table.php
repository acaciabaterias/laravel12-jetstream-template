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
        Schema::create('estoque_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bateria_id')->constrained('baterias')->onDelete('restrict');
            $table->foreignId('filial_id')->constrained('filiais')->onDelete('cascade');
            $table->foreignId('deposito_id')->constrained('depositos')->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('tipo'); // entrada, saida
            $table->integer('quantidade');
            $table->string('origem'); // NF, Ajuste, Venda
            $table->string('referencia')->nullable(); // NFe XML key, Justificativa do Ajuste, ID Venda
            $table->timestamp('data')->useCurrent();
            $table->timestamps();

            $table->index(['bateria_id', 'deposito_id']);
            $table->index('filial_id');
            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estoque_movimentacaos');
    }
};
