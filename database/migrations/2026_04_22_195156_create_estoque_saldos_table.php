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
        Schema::create('estoque_saldos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bateria_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deposito_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantidade_atual')->default(0);
            $table->timestamps();

            $table->unique(['bateria_id', 'deposito_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estoque_saldos');
    }
};
