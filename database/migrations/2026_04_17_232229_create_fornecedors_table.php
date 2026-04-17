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
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cnpj')->nullable();
            $table->decimal('saldo_sucata_kg', 12, 2)->default(0);
            $table->decimal('saldo_sucata_financeiro', 12, 2)->default(0);
            $table->foreignId('filial_id')->constrained('filiais')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();

            $table->index('filial_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fornecedors');
    }
};
