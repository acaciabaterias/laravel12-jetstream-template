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
        Schema::create('indice_retornos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bateria_id')->constrained('baterias')->onDelete('cascade');
            $table->string('periodo'); // YYYY-MM
            $table->integer('total_vendidas')->default(0);
            $table->integer('total_garantias')->default(0);
            $table->decimal('indice_calculado', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['bateria_id', 'periodo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indice_retornos');
    }
};
