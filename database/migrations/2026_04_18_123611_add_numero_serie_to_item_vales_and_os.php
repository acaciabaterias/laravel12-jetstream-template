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
        Schema::table('item_vales', function (Blueprint $table) {
            $table->string('numero_serie')->nullable()->index()->after('bateria_id');
        });

        Schema::table('ordem_servico_garantias', function (Blueprint $table) {
            $table->string('numero_serie')->nullable()->index()->after('bateria_id');
        });
    }

    public function down(): void
    {
        Schema::table('item_vales', function (Blueprint $table) {
            $table->dropColumn('numero_serie');
        });

        Schema::table('ordem_servico_garantias', function (Blueprint $table) {
            $table->dropColumn('numero_serie');
        });
    }
};
