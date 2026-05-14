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
        if (Schema::connection($this->connection)->hasTable('theme_rollback_evidences')) {
            return;
        }

        Schema::connection($this->connection)->create('theme_rollback_evidences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_theme_version_id')->constrained('tenant_theme_versions')->cascadeOnDelete();
            $table->foreignId('restored_theme_version_id')->nullable()->constrained('tenant_theme_versions')->nullOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->string('reason', 255);
            if (DB::connection('central')->getDriverName() === 'pgsql') {
                $table->jsonb('evidence_payload')->default(DB::raw("'{}'::jsonb"));
            } else {
                $table->json('evidence_payload')->nullable();
            }
            $table->timestampTz('rolled_back_at');
            $table->timestampsTz();

            $table->index(['tenant_theme_version_id', 'rolled_back_at']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('theme_rollback_evidences');
    }
};
