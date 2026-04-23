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
        Schema::create('reservas_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vale_id')->constrained('vales')->cascadeOnDelete();
            $table->foreignId('item_vale_id')->constrained('itens_vale')->cascadeOnDelete();
            $table->foreignId('bateria_id')->constrained('baterias')->cascadeOnDelete();
            $table->foreignId('deposito_id')->constrained('depositos')->cascadeOnDelete();
            $table->unsignedInteger('quantidade');
            $table->string('status')->default('reservada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas_estoque');
    }
};
