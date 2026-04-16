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
        Schema::create('filiais', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cnpj')->unique();
            $table->boolean('active')->default(true);
            
            // Campos de assinatura
            $table->string('subdominio', 50)->unique()->nullable();        // loja1.erp.com
            $table->string('dominio_personalizado', 100)->unique()->nullable(); // bateriasjoao.com.br
            $table->string('email_contato', 100);
            $table->string('telefone', 20);

            // Campos de plano e status
            $table->enum('plano', ['essential', 'pro', 'enterprise'])->default('essential');
            $table->enum('status_assinatura', ['trial', 'active', 'expired', 'cancelled'])->default('trial');
            $table->date('trial_ends_at')->nullable();
            $table->date('subscription_ends_at')->nullable();

            // Campos Stripe (faturamento)
            $table->string('stripe_customer_id')->nullable()->unique();
            $table->string('stripe_subscription_id')->nullable()->unique();

            // Limites do plano (desnormalizado para performance)
            $table->integer('max_usuarios')->default(3);
            $table->integer('max_estoque_itens')->default(500);
            $table->boolean('has_support_priority')->default(false);
            $table->boolean('has_white_label')->default(false);
            $table->boolean('has_api_integration')->default(false);

            // Campos de configuração
            $table->json('configuracoes')->nullable(); // JSON para configurações extras
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filiais');
    }
};
