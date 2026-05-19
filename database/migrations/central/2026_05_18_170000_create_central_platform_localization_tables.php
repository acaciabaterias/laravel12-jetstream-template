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
        Schema::connection($this->connection)->table('usuarios_plataforma', function (Blueprint $table): void {
            if (! Schema::connection($this->connection)->hasColumn('usuarios_plataforma', 'preferred_locale')) {
                $table->string('preferred_locale', 12)->nullable()->after('ativo');
            }
        });

        Schema::connection($this->connection)->create('platform_locale_publication_records', function (Blueprint $table): void {
            $table->id();
            $table->string('release_key', 120)->unique();
            $table->string('status', 20)->default('draft');
            $table->string('default_locale', 12);
            $table->string('fallback_locale', 12);
            $this->jsonColumn($table, 'supported_locales');
            $this->jsonColumn($table, 'coverage_snapshot');
            $table->foreignId('published_by')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->foreignId('rolled_back_by')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('rolled_back_at')->nullable();
            $table->foreignId('superseded_by_publication_id')->nullable()->constrained('platform_locale_publication_records')->nullOnDelete();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['status', 'published_at']);
        });

        Schema::connection($this->connection)->create('platform_locale_missing_key_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('platform_locale_publication_record_id')->nullable()->constrained('platform_locale_publication_records')->nullOnDelete();
            $table->string('locale_code', 12);
            $table->string('translation_key', 255);
            $table->string('context_group', 60);
            $table->string('severity', 20)->default('warning');
            $table->string('resolution_status', 20)->default('open');
            $table->timestampTz('detected_at');
            $table->timestampTz('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['locale_code', 'severity', 'resolution_status']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table platform_locale_publication_records add constraint platform_locale_publication_records_status_check check (status in ('draft', 'active', 'superseded', 'rolled_back'))");
            DB::connection($this->connection)->statement("alter table platform_locale_missing_key_reports add constraint platform_locale_missing_key_reports_severity_check check (severity in ('warning', 'critical'))");
            DB::connection($this->connection)->statement("alter table platform_locale_missing_key_reports add constraint platform_locale_missing_key_reports_resolution_status_check check (resolution_status in ('open', 'rolled_back', 'accepted'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('platform_locale_missing_key_reports');
        Schema::connection($this->connection)->dropIfExists('platform_locale_publication_records');

        Schema::connection($this->connection)->table('usuarios_plataforma', function (Blueprint $table): void {
            if (Schema::connection($this->connection)->hasColumn('usuarios_plataforma', 'preferred_locale')) {
                $table->dropColumn('preferred_locale');
            }
        });
    }

    private function usesPostgres(): bool
    {
        return DB::connection($this->connection)->getDriverName() === 'pgsql';
    }

    private function jsonColumn(Blueprint $table, string $column, bool $nullable = false): void
    {
        if ($this->usesPostgres()) {
            $definition = $table->jsonb($column);

            if ($nullable) {
                $definition->nullable();
            } else {
                $definition->default(DB::raw("'{}'::jsonb"));
            }

            return;
        }

        $definition = $table->json($column);

        if ($nullable) {
            $definition->nullable();
        }
    }
};
