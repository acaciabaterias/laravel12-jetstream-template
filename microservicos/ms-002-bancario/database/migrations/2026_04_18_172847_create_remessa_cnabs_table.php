<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remessa_cnabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banco_id')->constrained('banco_perfils');
            $table->string('arquivo_nome');
            $table->longText('arquivo_base64')->nullable();
            $table->string('tipo', 10);
            $table->string('status')->default('gerado');
            $table->integer('registros_total')->default(0);
            $table->integer('registros_ok')->default(0);
            $table->integer('registros_erro')->default(0);
            $table->timestamps();
        });

        Schema::create('webhook_recebidos', function (Blueprint $table) {
            $table->id();
            $table->string('banco_slug');
            $table->json('payload_raw');
            $table->string('evento')->nullable();
            $table->boolean('processado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_recebidos');
        Schema::dropIfExists('remessa_cnabs');
    }
};
