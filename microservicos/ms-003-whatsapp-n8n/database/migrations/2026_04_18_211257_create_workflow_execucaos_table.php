<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_execucaos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('workflow_name');
            $table->string('evento_trigger');
            $table->string('status');
            $table->json('payload_entrada')->nullable();
            $table->text('mensagem_enviada')->nullable();
            $table->string('canal')->default('whatsapp');
            $table->string('destinatario')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('template_mensagens', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('canal')->default('whatsapp');
            $table->text('conteudo_template');
            $table->json('variaveis')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_mensagens');
        Schema::dropIfExists('workflow_execucaos');
    }
};
