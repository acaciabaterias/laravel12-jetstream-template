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
        if (Schema::connection($this->connection)->hasTable('theme_publication_records')) {
            return;
        }

        Schema::connection($this->connection)->create('theme_publication_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_theme_version_id')->constrained('tenant_theme_versions')->cascadeOnDelete();
            $table->string('environment', 40)->default('staging');
            $table->foreignId('operator_id')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->boolean('validation_passed')->default(false);
            if (DB::connection('central')->getDriverName() === 'pgsql') {
                $table->jsonb('validation_messages')->default(DB::raw("'[]'::jsonb"));
                $table->jsonb('published_snapshot')->default(DB::raw("'{}'::jsonb"));
            } else {
                $table->json('validation_messages')->nullable();
                $table->json('published_snapshot')->nullable();
            }
            $table->string('status', 20)->default('pending');
            $table->timestampTz('published_at')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_theme_version_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('theme_publication_records');
    }
};
