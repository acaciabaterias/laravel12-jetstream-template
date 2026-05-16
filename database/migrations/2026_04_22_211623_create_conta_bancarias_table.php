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
        Schema::create('contas_bancarias', function (Blueprint $table) {
            $table->id();
            $table->string('banco');
            $table->string('agencia');
            $table->string('conta');
            $table->string('tipo');
            $table->text('token_api')->nullable();
            $table->string('status')->default('ativa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_bancarias');
    }
};
