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
        Schema::create('rota_entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entregador_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('filial_id')->constrained('filiais')->onDelete('cascade');
            $table->date('data_rota');
            $table->string('veiculo')->nullable();
            $table->string('status')->default('rascunho'); // rascunho, ativa, concluida, cancelada
            $table->text('observacoes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['filial_id', 'data_rota']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rota_entregas');
    }
};
