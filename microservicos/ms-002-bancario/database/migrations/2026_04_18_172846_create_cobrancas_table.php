<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cobrancas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('idempotency_key')->unique();
            $table->unsignedBigInteger('erp_fatura_id');
            $table->foreignId('banco_id')->constrained('banco_perfils');
            $table->string('tipo', 20);
            $table->decimal('valor', 15, 2);
            $table->date('vencimento')->nullable();
            $table->string('nosso_numero')->nullable()->index();
            $table->string('linha_digitavel')->nullable();
            $table->string('codigo_barras')->nullable();
            $table->text('pdf_url')->nullable();
            $table->text('qrcode_pix')->nullable();
            $table->longText('qr_code_imagem_base64')->nullable();
            $table->text('link_pagamento')->nullable();
            $table->string('txid')->nullable()->index();
            $table->string('status')->default('pendente')->index();
            $table->timestamp('pago_em')->nullable();
            $table->decimal('pago_valor', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobrancas');
    }
};
