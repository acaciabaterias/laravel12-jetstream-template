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
        Schema::create('fechamentos_contabeis', function (Blueprint $table) {
            $table->id();
            $table->string('competencia');
            $table->string('status')->default('aberto');
            $table->timestamp('fechado_em')->nullable();
            $table->foreignId('fechado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fechamentos_contabeis');
    }
};
