<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banco_providers', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo_banco', 10);
            $table->string('provider');
            $table->string('api_client_id')->nullable();
            $table->text('api_client_secret_encrypted')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banco_providers');
    }
};
