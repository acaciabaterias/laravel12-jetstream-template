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
        Schema::create('itens_vale', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vale_id')->constrained('vales')->cascadeOnDelete();
            $table->foreignId('bateria_id')->constrained('baterias')->cascadeOnDelete();
            $table->unsignedInteger('quantidade');
            $table->decimal('preco_unitario_original', 12, 2);
            $table->decimal('preco_unitario_final', 12, 2);
            $table->boolean('flag_devolveu_sucata')->default(true);
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_vale');
    }
};
