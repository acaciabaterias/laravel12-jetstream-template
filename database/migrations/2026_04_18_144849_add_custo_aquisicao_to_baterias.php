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
            $table->decimal('custo_aquisicao', 10, 2)->default(0)->after('preco_venda');
        });
    }

    public function down(): void
    {
        Schema::table('baterias', function (Blueprint $table) {
            $table->dropColumn('custo_aquisicao');
        });
    }
};
