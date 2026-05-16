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
        Schema::create('pedidos_venda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vale_id')->constrained('vales')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->timestamp('data_emissao');
            $table->decimal('valor_total', 12, 2);
            $table->string('status')->default('faturado');
            $table->string('nf_referencia')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos_venda');
    }
};
