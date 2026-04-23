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
        Schema::create('indices_retorno_produto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bateria_id')->constrained('baterias')->cascadeOnDelete();
            $table->date('periodo_inicio');
            $table->date('periodo_fim');
            $table->unsignedInteger('total_vendidas')->default(0);
            $table->unsignedInteger('total_garantias')->default(0);
            $table->decimal('indice_calculado', 8, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indices_retorno_produto');
    }
};
