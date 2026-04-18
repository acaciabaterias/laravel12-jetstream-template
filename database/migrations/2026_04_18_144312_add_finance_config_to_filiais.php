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
        Schema::table('filiais', function (Blueprint $table) {
            $table->enum('comissao_tipo', ['fixo', 'percentual'])->default('percentual');
            $table->decimal('comissao_valor', 10, 2)->default(0);
            $table->date('data_fechamento_contabil')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('filiais', function (Blueprint $table) {
            $table->dropColumn(['comissao_tipo', 'comissao_valor', 'data_fechamento_contabil']);
        });
    }
};
