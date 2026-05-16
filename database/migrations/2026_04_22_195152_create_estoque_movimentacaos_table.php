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
            $table->foreignId('bateria_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deposito_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tipo_operacao');
            $table->string('origem')->nullable();
            $table->unsignedInteger('quantidade');
            $table->text('justificativa')->nullable();
            $table->timestamp('data_movimentacao');
            $table->timestamps();

            $table->index(['deposito_id', 'bateria_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estoque_movimentacoes');
    }
};
