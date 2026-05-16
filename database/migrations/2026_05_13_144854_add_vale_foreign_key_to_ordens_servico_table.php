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
        if (! Schema::hasTable('ordens_servico') || ! Schema::hasTable('vales')) {
            return;
        }

        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->foreign('vale_id')
                ->references('id')
                ->on('vales')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('ordens_servico')) {
            return;
        }

        Schema::table('ordens_servico', function (Blueprint $table) {
            $table->dropForeign(['vale_id']);
        });
    }
};
