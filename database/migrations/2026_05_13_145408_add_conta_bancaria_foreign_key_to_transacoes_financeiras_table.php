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
        if (! Schema::hasTable('transacoes_financeiras') || ! Schema::hasTable('contas_bancarias')) {
            return;
        }

        Schema::table('transacoes_financeiras', function (Blueprint $table) {
            $table->foreign('conta_bancaria_id')
                ->references('id')
                ->on('contas_bancarias')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('transacoes_financeiras')) {
            return;
        }

        Schema::table('transacoes_financeiras', function (Blueprint $table) {
            $table->dropForeign(['conta_bancaria_id']);
        });
    }
};
