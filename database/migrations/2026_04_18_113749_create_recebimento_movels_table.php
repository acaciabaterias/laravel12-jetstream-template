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
        Schema::create('recebimento_movels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ponto_entrega_id')->constrained('ponto_entregas')->onDelete('cascade');
            $table->foreignId('filial_id')->constrained('filiais')->onDelete('cascade');
            $table->decimal('valor', 10, 2);
            $table->string('metodo')->default('dinheiro'); // dinheiro, pix, cartao_debito, cartao_credito
            $table->string('status_sincronizado')->default('pendente'); // pendente, sincronizado, contestavel
            $table->string('comprovante_url')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->index('status_sincronizado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recebimento_movels');
    }
};
