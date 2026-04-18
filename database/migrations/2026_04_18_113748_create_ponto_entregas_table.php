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
        Schema::create('ponto_entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rota_entrega_id')->constrained('rota_entregas')->onDelete('cascade');
            $table->foreignId('vale_id')->constrained('vales')->onDelete('restrict');
            $table->foreignId('filial_id')->constrained('filiais')->onDelete('cascade');
            $table->integer('ordem_parada')->default(1);
            $table->string('status')->default('pendente'); // pendente, em_transito, concluido, falhou
            $table->decimal('peso_sucata_coletado', 10, 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('checkin_at')->nullable();
            $table->timestamp('checkout_at')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->index(['filial_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ponto_entregas');
    }
};
