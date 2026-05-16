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
        Schema::create('boletos_orquestrados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vale_id')->constrained('vales')->cascadeOnDelete();
            $table->string('nosso_numero')->nullable();
            $table->string('linha_digitavel')->nullable();
            $table->string('pdf_url')->nullable();
            $table->string('status')->default('pendente');
            $table->string('identificador_externo')->nullable();
            $table->string('idempotency_key')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletos_orquestrados');
    }
};
