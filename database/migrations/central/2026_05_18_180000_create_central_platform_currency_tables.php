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
            if (! Schema::connection($this->connection)->hasColumn('usuarios_plataforma', 'preferred_currency')) {
                $table->string('preferred_currency', 3)->nullable()->after('preferred_locale');
            }
        });

        Schema::connection($this->connection)->create('platform_currency_catalog_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('currency_code', 3)->unique();
            $table->string('display_name', 120);
            $table->string('symbol', 12);
            $table->unsignedTinyInteger('decimal_scale')->default(2);
            $table->boolean('is_enabled')->default(true);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('platform_currency_publication_records', function (Blueprint $table): void {
            $table->id();
            $table->string('release_key', 120)->unique();
            $table->string('status', 20)->default('draft');
            $table->string('base_currency_code', 3);
            $table->string('default_currency_code', 3);
            $this->jsonColumn($table, 'supported_currencies');
            $this->jsonColumn($table, 'rate_snapshot');
            $this->jsonColumn($table, 'coverage_snapshot');
            $table->foreignId('published_by')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->foreignId('rolled_back_by')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('rolled_back_at')->nullable();
            $table->foreignId('superseded_by_publication_id')->nullable()->constrained('platform_currency_publication_records')->nullOnDelete();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['status', 'published_at']);
        });

        Schema::connection($this->connection)->create('platform_currency_rate_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('platform_currency_publication_record_id')->constrained('platform_currency_publication_records')->cascadeOnDelete();
            $table->string('currency_code', 3);
            $table->decimal('rate_against_base', 18, 8);
            $table->decimal('inverse_rate', 18, 8)->nullable();
            $table->timestampTz('effective_at');
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->unique(['platform_currency_publication_record_id', 'currency_code'], 'platform_currency_rates_publication_currency_unique');
        });

        Schema::connection($this->connection)->create('platform_currency_issue_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('platform_currency_publication_record_id')->nullable()->constrained('platform_currency_publication_records')->nullOnDelete();
            $table->string('currency_code', 3);
            $table->string('issue_type', 40);
            $table->string('severity', 20)->default('warning');
            $table->string('resolution_status', 20)->default('open');
            $table->timestampTz('detected_at');
            $table->timestampTz('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['currency_code', 'severity', 'resolution_status']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table platform_currency_publication_records add constraint platform_currency_publication_records_status_check check (status in ('draft', 'active', 'superseded', 'rolled_back'))");
            DB::connection($this->connection)->statement('alter table platform_currency_rate_entries add constraint platform_currency_rate_entries_rate_check check (rate_against_base > 0)');
            DB::connection($this->connection)->statement("alter table platform_currency_issue_reports add constraint platform_currency_issue_reports_severity_check check (severity in ('warning', 'critical'))");
            DB::connection($this->connection)->statement("alter table platform_currency_issue_reports add constraint platform_currency_issue_reports_resolution_status_check check (resolution_status in ('open', 'resolved', 'rolled_back'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('platform_currency_issue_reports');
        Schema::connection($this->connection)->dropIfExists('platform_currency_rate_entries');
        Schema::connection($this->connection)->dropIfExists('platform_currency_publication_records');
        Schema::connection($this->connection)->dropIfExists('platform_currency_catalog_entries');

        Schema::connection($this->connection)->table('usuarios_plataforma', function (Blueprint $table): void {
            if (Schema::connection($this->connection)->hasColumn('usuarios_plataforma', 'preferred_currency')) {
                $table->dropColumn('preferred_currency');
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
