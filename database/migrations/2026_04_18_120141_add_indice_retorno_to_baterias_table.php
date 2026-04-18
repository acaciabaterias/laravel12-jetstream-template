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
        Schema::table('baterias', function (Blueprint $table) {
            $table->decimal('indice_retorno', 5, 2)->default(0)->after('valor_base_sucata_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('baterias', function (Blueprint $table) {
            //
        });
    }
};
