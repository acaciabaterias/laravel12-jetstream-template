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
        Schema::create('transacoes_financeiras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_bancaria_id')->constrained('contas_bancarias')->cascadeOnDelete();
            $table->string('tipo');
            $table->decimal('valor', 12, 2);
            $table->timestamp('data_transacao');
            $table->boolean('status_conciliado')->default(false);
            $table->string('origem_tipo')->nullable();
            $table->unsignedBigInteger('origem_id')->nullable();
            $table->string('descricao')->nullable();
            $table->string('identificador_externo')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacoes_financeiras');
    }
};
