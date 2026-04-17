<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    

    public function up(): void
    {
        Schema::create('assinaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('plano_id')->constrained('planos');
            $table->enum('status', ['trial', 'active', 'expired', 'cancelled']);
            $table->date('data_inicio');
            $table->date('data_proximo_ciclo');
            $table->date('data_termino')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assinaturas');
    }
};
