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
        if (! Schema::hasTable('estoque_movimentacoes') || ! Schema::hasTable('depositos')) {
            return;
        }

        Schema::table('estoque_movimentacoes', function (Blueprint $table) {
            $table->foreign('deposito_id')
                ->references('id')
                ->on('depositos')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('estoque_movimentacoes')) {
            return;
        }

        Schema::table('estoque_movimentacoes', function (Blueprint $table) {
            $table->dropForeign(['deposito_id']);
        });
    }
};
