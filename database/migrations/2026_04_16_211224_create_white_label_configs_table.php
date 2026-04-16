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
        Schema::create('white_label_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filial_id')->constrained('filiais')->onDelete('cascade');
            
            // Branding
            $table->string('logo_url')->nullable();
            $table->string('favicon_url')->nullable();
            $table->string('cor_primaria', 7)->default('#3b82f6');
            $table->string('cor_secundaria', 7)->default('#10b981');
            $table->string('cor_fundo', 7)->default('#f9fafb');
            
            // Textos e customizações
            $table->string('titulo_login')->nullable();
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();
            $table->string('template_nome')->default('default');
            $table->boolean('mostrar_marca_plataforma')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('white_label_configs');
    }
};
