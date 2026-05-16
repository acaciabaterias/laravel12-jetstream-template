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
        if (Schema::connection($this->connection)->hasTable('tenant_theme_versions')) {
            return;
        }

        Schema::connection($this->connection)->create('tenant_theme_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('brand_identity_profile_id')->constrained('brand_identity_profiles')->cascadeOnDelete();
            $table->string('version_label', 80);
            if (DB::connection('central')->getDriverName() === 'pgsql') {
                $table->jsonb('theme_tokens')->default(DB::raw("'{}'::jsonb"));
                $table->jsonb('navigation_preferences')->default(DB::raw("'{}'::jsonb"));
                $table->jsonb('validation_summary')->default(DB::raw("'{}'::jsonb"));
            } else {
                $table->json('theme_tokens')->nullable();
                $table->json('navigation_preferences')->nullable();
                $table->json('validation_summary')->nullable();
            }
            $table->string('status', 20)->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('rolled_back_at')->nullable();
            $table->timestampsTz();

            $table->index(['brand_identity_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('tenant_theme_versions');
    }
};
