<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contingencia_queue', function (Blueprint $table) {
            $table->id();
            $table->uuid('nota_id');
            $table->string('motivo')->nullable();
            $table->integer('tentativas_realizadas')->default(0);
            $table->timestamp('ultima_tentativa')->nullable();
            $table->timestamp('proxima_tentativa')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contingencia_queue');
    }
};
