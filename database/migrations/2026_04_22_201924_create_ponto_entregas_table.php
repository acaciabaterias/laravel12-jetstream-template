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
        Schema::create('pontos_entrega', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rota_entrega_id')->constrained('rotas_entrega')->cascadeOnDelete();
            $table->foreignId('vale_id')->nullable()->constrained('vales')->nullOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('endereco_entrega');
            $table->unsignedInteger('ordem_parada');
            $table->string('status')->default('planejado');
            $table->decimal('peso_sucata_coletado', 10, 2)->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pontos_entrega');
    }
};
