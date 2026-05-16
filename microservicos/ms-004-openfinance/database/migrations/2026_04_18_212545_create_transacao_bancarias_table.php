<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transacao_bancarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consentimento_id')->constrained('consentimentos');
            $table->string('tx_id_original');
            $table->date('data_lancamento');
            $table->date('data_valor')->nullable();
            $table->string('descricao');
            $table->decimal('valor', 15, 2);
            $table->string('tipo');
            $table->string('categoria')->nullable();
            $table->string('conta_origem')->nullable();
            $table->string('conta_destino')->nullable();
            $table->string('deduplicacao_hash')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transacao_bancarias');
    }
};
