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
        Schema::create('sync_eventos', function (Blueprint $table) {
            $table->id();
            $table->uuid('dispositivo_uuid');
            $table->string('entidade_tipo');
            $table->unsignedBigInteger('entidade_id')->nullable();
            $table->string('payload_hash')->unique();
            $table->json('payload');
            $table->string('status')->default('pendente');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_eventos');
    }
};
