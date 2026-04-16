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
        Schema::create('planos_assinatura', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 50);  // Essential, Pro, Enterprise
            $table->string('slug')->unique();  // essential, pro, enterprise
            $table->decimal('preco_mensal', 10, 2);
            $table->decimal('preco_anual', 10, 2)->nullable();
            
            // Features
            $table->integer('max_usuarios');
            $table->integer('max_estoque_itens');
            $table->boolean('has_white_label')->default(false);
            $table->boolean('has_api_integration')->default(false);
            $table->boolean('has_support_priority')->default(false);
            
            // Stripe
            $table->string('stripe_price_id_mensal')->nullable();
            $table->string('stripe_price_id_anual')->nullable();
            
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planos_assinatura');
    }
};
