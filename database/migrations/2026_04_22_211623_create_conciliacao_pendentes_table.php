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
        Schema::create('conciliacoes_pendentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transacao_financeira_id')->constrained('transacoes_financeiras')->cascadeOnDelete();
            $table->string('motivo');
            $table->json('payload_bancario')->nullable();
            $table->string('status')->default('pendente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conciliacoes_pendentes');
    }
};
