<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banco_perfils', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo_banco', 3);
            $table->string('agencia');
            $table->string('conta');
            $table->string('convenio')->nullable();
            $table->string('ambiente')->default('homolog');
            $table->json('credenciais_json_encrypted')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remessa_cnabs');
        Schema::dropIfExists('cobrancas');
        Schema::dropIfExists('banco_perfils');
    }
};
