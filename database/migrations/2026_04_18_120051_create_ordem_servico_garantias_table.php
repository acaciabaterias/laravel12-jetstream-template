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
        Schema::create('ordem_servico_garantias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('bateria_id')->constrained('baterias')->onDelete('restrict');
            $table->foreignId('vale_original_id')->nullable()->constrained('vales')->onDelete('set null');
            $table->foreignId('filial_id')->constrained('filiais')->onDelete('cascade');
            $table->string('status')->default('aberta'); // aberta, em_avaliacao, pronta, concluida, negada
            $table->text('laudo')->nullable();
            $table->string('resultado')->nullable(); // procedente, improcedente
            $table->timestamp('data_abertura')->useCurrent();
            $table->timestamp('data_conclusao')->nullable();
            $table->timestamps();

            $table->index(['filial_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordem_servico_garantias');
    }
};
