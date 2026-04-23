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
        Schema::create('ordens_servico_garantia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('bateria_id')->constrained('baterias')->cascadeOnDelete();
            $table->foreignId('vale_original_id')->nullable()->constrained('vales')->nullOnDelete();
            $table->timestamp('data_abertura');
            $table->string('status')->default('aberta');
            $table->text('laudo')->nullable();
            $table->string('resultado')->nullable();
            $table->decimal('cobranca_valor', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens_servico_garantia');
    }
};
