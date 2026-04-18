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
        Schema::create('ordem_servicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vale_id')->constrained('vales')->onDelete('restrict');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('filial_id')->constrained('filiais')->onDelete('cascade');
            $table->timestamp('data_abertura')->useCurrent();
            $table->string('status')->default('aberta'); // aberta, em_andamento, concluida, cancelada
            $table->string('prioridade')->default('normal');
            $table->string('tecnico_responsavel')->nullable();
            $table->text('laudo')->nullable();
            $table->text('observacoes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('filial_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordem_servicos');
    }
};
