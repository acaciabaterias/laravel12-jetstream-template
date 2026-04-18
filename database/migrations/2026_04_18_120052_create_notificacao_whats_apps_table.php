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
        Schema::create('notificacao_whats_apps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_garantia_id')->constrained('ordem_servico_garantias')->onDelete('cascade');
            $table->string('cliente_telefone');
            $table->string('status')->default('pendente'); // pendente, enviado, falha
            $table->text('mensagem');
            $table->timestamp('data_envio')->nullable();
            $table->timestamps();

            $table->index(['os_garantia_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacao_whats_apps');
    }
};
