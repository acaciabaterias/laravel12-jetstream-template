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
        if (! Schema::hasTable('baterias_emprestimo') || ! Schema::hasTable('ordens_servico_garantia')) {
            return;
        }

        Schema::table('baterias_emprestimo', function (Blueprint $table) {
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
        if (! Schema::hasTable('baterias_emprestimo')) {
            return;
        }

        Schema::table('baterias_emprestimo', function (Blueprint $table) {
            $table->dropForeign(['os_garantia_id']);
        });
    }
};
