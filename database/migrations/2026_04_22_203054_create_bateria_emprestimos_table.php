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
        Schema::create('baterias_emprestimo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_garantia_id');
            $table->foreignId('bateria_usada_id')->constrained('baterias')->cascadeOnDelete();
            $table->timestamp('data_retirada');
            $table->timestamp('data_devolucao_prevista');
            $table->timestamp('data_devolucao_real')->nullable();
            $table->string('termo_arquivo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baterias_emprestimo');
    }
};
