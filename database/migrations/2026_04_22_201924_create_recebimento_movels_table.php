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
        Schema::create('recebimentos_moveis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ponto_entrega_id')->constrained('pontos_entrega')->cascadeOnDelete();
            $table->decimal('valor', 12, 2);
            $table->string('metodo_pagamento');
            $table->boolean('status_sincronizado')->default(false);
            $table->string('comprovante_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recebimentos_moveis');
    }
};
