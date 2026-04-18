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
        Schema::create('bateria_emprestimos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_garantia_id')->constrained('ordem_servico_garantias')->onDelete('cascade');
            $table->foreignId('bateria_id')->constrained('baterias')->onDelete('restrict'); 
            $table->date('data_retirada');
            $table->date('data_devolucao_prevista');
            $table->date('data_devolucao_real')->nullable();
            $table->string('termo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bateria_emprestimos');
    }
};
