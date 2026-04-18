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
        Schema::create('vales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('vendedor_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('filial_id')->constrained('filiais')->onDelete('cascade');
            $table->string('status')->default('aberto'); // aberto, faturado, cancelado
            $table->text('observacoes')->nullable();
            $table->timestamp('data_faturamento')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('filial_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vales');
    }
};
