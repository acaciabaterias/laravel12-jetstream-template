<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fila_notificacaos', function (Blueprint $table) {
            $table->id();
            $table->string('evento');
            $table->string('destinatario');
            $table->string('canal')->default('whatsapp');
            $table->json('payload');
            $table->string('status')->default('pendente');
            $table->timestamp('agendado_para')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fila_notificacaos');
    }
};
