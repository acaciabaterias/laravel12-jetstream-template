<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rota_id')->constrained('rotas');
            $table->integer('ordem');
            $table->unsignedBigInteger('entrega_id');
            $table->string('cliente_nome');
            $table->text('endereco');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->timestamp('eta_chegada')->nullable();
            $table->string('status')->default('pendente');
            $table->timestamp('chegada_real')->nullable();
            $table->timestamp('saida_real')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paradas');
    }
};
