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
        Schema::create('cnab_remessas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_arquivo');
            $table->string('nome_arquivo');
            $table->string('status')->default('gerada');
            $table->string('arquivo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cnab_remessas');
    }
};
