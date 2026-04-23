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
        Schema::create('filas_contingencia', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_integracao');
            $table->json('payload');
            $table->unsignedInteger('tentativas')->default(0);
            $table->timestamp('proxima_tentativa')->nullable();
            $table->string('status')->default('pendente');
            $table->text('ultimo_erro')->nullable();
            $table->string('idempotency_key')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filas_contingencia');
    }
};
