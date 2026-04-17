<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    

    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('cnpj', 18)->unique();
            $table->string('razao_social', 150);
            $table->string('nome_fantasia', 100)->nullable();
            $table->string('email_contato', 100);
            $table->string('telefone', 20);
            $table->string('subdominio', 50)->unique();
            $table->string('plano', 20)->default('essential');
            $table->enum('status', ['trial', 'active', 'expired', 'cancelled'])->default('trial');
            $table->date('trial_ends_at')->nullable();
            $table->date('subscription_ends_at')->nullable();

            // Dados de conexão Supabase
            $table->string('supabase_project_ref')->unique();
            $table->string('supabase_url');
            $table->string('supabase_db_host');
            $table->string('supabase_db_password');
            $table->text('supabase_anon_key');
            $table->text('supabase_service_role_key');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
