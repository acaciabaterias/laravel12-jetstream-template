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
        Schema::create('xml_importacoes', function (Blueprint $table) {
            $table->id();
            $table->string('chave_nfe')->unique();
            $table->unsignedBigInteger('fornecedor_id')->nullable();
            $table->string('status')->default('pendente');
            $table->text('log_erros')->nullable();
            $table->json('payload_xml')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xml_importacoes');
    }
};
