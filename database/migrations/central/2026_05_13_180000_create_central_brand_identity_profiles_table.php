<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasTable('brand_identity_profiles')) {
            return;
        }

        Schema::connection($this->connection)->create('brand_identity_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('brand_name', 120);
            $table->string('brand_slug', 120)->unique();
            $table->string('login_title', 150)->nullable();
            $table->string('default_font_family', 120)->default('Poppins');
            $table->unsignedBigInteger('active_theme_version_id')->nullable();
            if (DB::connection('central')->getDriverName() === 'pgsql') {
                $table->jsonb('default_theme_tokens')->default(DB::raw("'{}'::jsonb"));
            } else {
                $table->json('default_theme_tokens')->nullable();
            }
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->unique('cliente_id');
            $table->index(['cliente_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('brand_identity_profiles');
    }
};
