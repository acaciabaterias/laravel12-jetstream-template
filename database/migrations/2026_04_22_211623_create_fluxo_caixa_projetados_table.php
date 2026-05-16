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
        Schema::create('fluxos_caixa_projetado', function (Blueprint $table) {
            $table->id();
            $table->date('data_referencia');
            $table->decimal('saldo_inicial', 12, 2)->default(0);
            $table->decimal('total_receber', 12, 2)->default(0);
            $table->decimal('total_pagar', 12, 2)->default(0);
            $table->decimal('saldo_projetado', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fluxos_caixa_projetado');
    }
};
