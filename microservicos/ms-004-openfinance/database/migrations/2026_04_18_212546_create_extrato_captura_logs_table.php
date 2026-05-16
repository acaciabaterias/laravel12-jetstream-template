<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extrato_captura_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consentimento_id')->constrained('consentimentos');
            $table->string('status');
            $table->integer('total_transacoes')->default(0);
            $table->timestamp('periodo_de')->nullable();
            $table->timestamp('periodo_ate')->nullable();
            $table->integer('duracao_ms')->default(0);
            $table->text('erro_descricao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extrato_captura_logs');
    }
};
