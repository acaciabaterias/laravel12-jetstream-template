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
        Schema::create('item_vales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vale_id')->constrained('vales')->onDelete('cascade');
            $table->foreignId('bateria_id')->constrained('baterias')->onDelete('restrict');
            $table->integer('quantidade');
            $table->decimal('preco_unitario_original', 10, 2);
            $table->decimal('preco_unitario_final', 10, 2);
            $table->boolean('flag_devolveu_sucata')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_vales');
    }
};
