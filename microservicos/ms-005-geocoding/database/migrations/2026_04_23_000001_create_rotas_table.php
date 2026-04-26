<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rotas', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id_externo');
            $table->string('base_operacional_id');
            $table->date('data_entrega');
            $table->string('status')->default('pendente');
            $table->json('paradas_json')->nullable();
            $table->decimal('distancia_total_km', 10, 2)->nullable();
            $table->integer('duracao_estimada_min')->nullable();
            $table->timestamp('otimizada_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rotas');
    }
};
