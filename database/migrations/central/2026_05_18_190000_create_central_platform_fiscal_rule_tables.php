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
        Schema::connection($this->connection)->create('fiscal_cfop_catalog_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('cfop_code', 4)->unique();
            $table->string('description', 255);
            $table->string('operation_direction', 20);
            $table->boolean('is_enabled')->default(true);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['operation_direction', 'is_enabled']);
        });

        Schema::connection($this->connection)->create('fiscal_operation_scenarios', function (Blueprint $table): void {
            $table->id();
            $table->string('scenario_key', 80)->unique();
            $table->string('display_name', 160);
            $table->string('operation_direction', 20);
            $table->boolean('is_required')->default(false);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['operation_direction', 'is_required']);
        });

        Schema::connection($this->connection)->create('fiscal_rule_publication_records', function (Blueprint $table): void {
            $table->id();
            $table->string('release_key', 120)->unique();
            $table->string('status', 20)->default('draft');
            $this->jsonColumn($table, 'supported_scenarios');
            $this->jsonColumn($table, 'catalog_snapshot');
            $this->jsonColumn($table, 'coverage_snapshot');
            $table->foreignId('published_by')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->foreignId('rolled_back_by')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->timestampTz('published_at')->nullable();
            $table->timestampTz('rolled_back_at')->nullable();
            $table->foreignId('superseded_by_publication_id')->nullable()->constrained('fiscal_rule_publication_records')->nullOnDelete();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['status', 'published_at']);
        });

        Schema::connection($this->connection)->create('fiscal_rule_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('fiscal_rule_publication_record_id')->constrained('fiscal_rule_publication_records')->cascadeOnDelete();
            $table->string('scenario_key', 80);
            $table->string('cfop_code', 4);
            $table->string('classification_code', 40)->nullable();
            $table->string('operation_direction', 20);
            $this->jsonColumn($table, 'validation_flags');
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->unique(['fiscal_rule_publication_record_id', 'scenario_key'], 'fiscal_rule_mappings_publication_scenario_unique');
        });

        Schema::connection($this->connection)->create('fiscal_rule_issue_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('fiscal_rule_publication_record_id')->nullable()->constrained('fiscal_rule_publication_records')->nullOnDelete();
            $table->string('scenario_key', 80);
            $table->string('issue_type', 40);
            $table->string('severity', 20)->default('warning');
            $table->string('resolution_status', 20)->default('open');
            $table->timestampTz('detected_at');
            $table->timestampTz('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $this->jsonColumn($table, 'issue_payload', nullable: true);
            $table->timestampsTz();

            $table->index(['scenario_key', 'severity', 'resolution_status']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table fiscal_cfop_catalog_entries add constraint fiscal_cfop_catalog_entries_operation_direction_check check (operation_direction in ('export', 'import', 'domestic_out', 'domestic_in'))");
            DB::connection($this->connection)->statement("alter table fiscal_operation_scenarios add constraint fiscal_operation_scenarios_operation_direction_check check (operation_direction in ('export', 'import', 'domestic_out', 'domestic_in'))");
            DB::connection($this->connection)->statement("alter table fiscal_rule_publication_records add constraint fiscal_rule_publication_records_status_check check (status in ('draft', 'active', 'superseded', 'rolled_back'))");
            DB::connection($this->connection)->statement("alter table fiscal_rule_mappings add constraint fiscal_rule_mappings_operation_direction_check check (operation_direction in ('export', 'import', 'domestic_out', 'domestic_in'))");
            DB::connection($this->connection)->statement("alter table fiscal_rule_issue_reports add constraint fiscal_rule_issue_reports_severity_check check (severity in ('warning', 'critical'))");
            DB::connection($this->connection)->statement("alter table fiscal_rule_issue_reports add constraint fiscal_rule_issue_reports_resolution_status_check check (resolution_status in ('open', 'resolved', 'rolled_back'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('fiscal_rule_issue_reports');
        Schema::connection($this->connection)->dropIfExists('fiscal_rule_mappings');
        Schema::connection($this->connection)->dropIfExists('fiscal_rule_publication_records');
        Schema::connection($this->connection)->dropIfExists('fiscal_operation_scenarios');
        Schema::connection($this->connection)->dropIfExists('fiscal_cfop_catalog_entries');
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
