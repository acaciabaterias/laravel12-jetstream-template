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
        if (! Schema::hasTable('geolocalizacao_eventos') || ! Schema::hasTable('pontos_entrega')) {
            return;
        }

        Schema::table('geolocalizacao_eventos', function (Blueprint $table) {
            $table->foreign('ponto_entrega_id')
                ->references('id')
                ->on('pontos_entrega')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('geolocalizacao_eventos')) {
            return;
        }

        Schema::table('geolocalizacao_eventos', function (Blueprint $table) {
            $table->dropForeign(['ponto_entrega_id']);
        });
    }
};
