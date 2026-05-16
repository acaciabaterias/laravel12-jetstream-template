<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 50);
            $table->string('slug')->unique();
            $table->decimal('preco_mensal', 10, 2);
            $table->integer('max_usuarios')->default(3);
            $table->integer('max_estoque_itens')->default(500);
            $table->boolean('has_white_label')->default(false);
            $table->boolean('has_support_priority')->default(false);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planos');
    }
};
