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
        Schema::create('conta_sucata_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->string('entidade_tipo');
            $table->unsignedBigInteger('entidade_id')->nullable();
            $table->string('tipo_movimento');
            $table->decimal('quantidade_kg', 10, 2);
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('saldo_resultante', 12, 2)->default(0);
            $table->string('origem')->nullable();
            $table->timestamps();

            $table->index(['entidade_tipo', 'entidade_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conta_sucata_movimentacoes');
    }
};
