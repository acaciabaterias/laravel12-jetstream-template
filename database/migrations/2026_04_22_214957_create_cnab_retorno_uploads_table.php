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
        Schema::create('cnab_retorno_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cnab_remessa_id')->nullable()->constrained('cnab_remessas')->nullOnDelete();
            $table->string('nome_arquivo');
            $table->string('status_processamento')->default('pendente');
            $table->text('log_processamento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cnab_retorno_uploads');
    }
};
