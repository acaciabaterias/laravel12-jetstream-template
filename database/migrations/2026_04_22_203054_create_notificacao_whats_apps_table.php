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
        Schema::create('notificacoes_whatsapp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_garantia_id');
            $table->string('cliente_telefone')->nullable();
            $table->string('status')->default('pendente');
            $table->text('mensagem');
            $table->timestamp('data_envio')->nullable();
            $table->string('identificador_externo')->nullable();
            $table->text('tracking_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacoes_whatsapp');
    }
};
