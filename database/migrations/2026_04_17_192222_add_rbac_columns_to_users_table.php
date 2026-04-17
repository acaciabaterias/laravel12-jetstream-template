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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('filial_id')->nullable()->index();
            $table->enum('papel', ['super_admin', 'dono', 'gestor', 'vendedor', 'tecnico', 'estoquista'])->default('vendedor')->index();
            $table->boolean('ativo')->default(true);

            // Unique index on email is usually already present, but T005 mentions to ensure it.
            // Email column might already be unique in default Jetstream. We can double check or not add it here.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['filial_id', 'papel', 'ativo']);
        });
    }
};
