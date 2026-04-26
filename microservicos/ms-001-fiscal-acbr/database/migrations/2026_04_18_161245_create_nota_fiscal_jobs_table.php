<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_fiscal_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('vale_id');
            $table->string('tipo');
            $table->json('payload');
            $table->text('xml_assinado')->nullable();
            $table->string('chave_acesso', 44)->nullable();
            $table->string('protocolo')->nullable();
            $table->string('status')->default('pending');
            $table->integer('tentativas')->default(0);
            $table->timestamp('proxima_tentativa')->nullable();
            $table->string('correlation_id')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_fiscal_jobs');
    }
};
