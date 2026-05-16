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
        Schema::create('margens_lucro_real', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bateria_id')->constrained('baterias')->cascadeOnDelete();
            $table->date('periodo_inicio');
            $table->date('periodo_fim');
            $table->decimal('valor_venda', 12, 2)->default(0);
            $table->decimal('custo_aquisicao', 12, 2)->default(0);
            $table->decimal('frete', 12, 2)->default(0);
            $table->decimal('imposto', 12, 2)->default(0);
            $table->decimal('comissao', 12, 2)->default(0);
            $table->decimal('margem_calculada', 8, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('margens_lucro_real');
    }
};
