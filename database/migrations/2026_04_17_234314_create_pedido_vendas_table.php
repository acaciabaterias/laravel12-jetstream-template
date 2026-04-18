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
        Schema::create('pedido_vendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vale_id')->constrained('vales')->onDelete('restrict');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('filial_id')->constrained('filiais')->onDelete('cascade');
            $table->timestamp('data_emissao')->useCurrent();
            $table->decimal('valor_total', 12, 2);
            $table->string('status')->default('pendente'); // pendente, pago, cancelado
            $table->string('nf_referencia')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('filial_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_vendas');
    }
};
