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
        if (! Schema::hasTable('notificacoes_whatsapp') || ! Schema::hasTable('ordens_servico_garantia')) {
            return;
        }

        Schema::table('notificacoes_whatsapp', function (Blueprint $table) {
            $table->foreign('os_garantia_id')
                ->references('id')
                ->on('ordens_servico_garantia')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('notificacoes_whatsapp')) {
            return;
        }

        Schema::table('notificacoes_whatsapp', function (Blueprint $table) {
            $table->dropForeign(['os_garantia_id']);
        });
    }
};
