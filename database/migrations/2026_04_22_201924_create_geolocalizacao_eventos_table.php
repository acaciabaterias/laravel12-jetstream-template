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
        Schema::create('geolocalizacao_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rota_entrega_id')->nullable()->constrained('rotas_entrega')->nullOnDelete();
            $table->foreignId('ponto_entrega_id')->nullable()->constrained('pontos_entrega')->nullOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('tipo_evento');
            $table->timestamp('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geolocalizacao_eventos');
    }
};
