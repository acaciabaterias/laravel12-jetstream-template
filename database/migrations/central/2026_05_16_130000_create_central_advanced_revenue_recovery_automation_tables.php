<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection($this->connection)->create('recovery_automation_policy_versions', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('name', 160);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft');
            $this->jsonColumn($table, 'scope_filters');
            $this->jsonColumn($table, 'guardrail_rules');
            $this->jsonColumn($table, 'fallback_matrix');
            $table->timestampTz('activation_started_at')->nullable();
            $table->timestampTz('activation_completed_at')->nullable();
            $table->foreignId('superseded_by_policy_version_id')->nullable()->constrained('recovery_automation_policy_versions')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->foreignId('rolled_back_by')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['status', 'created_at']);
        });

        Schema::connection($this->connection)->create('recovery_automation_experiments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recovery_automation_policy_version_id')->constrained('recovery_automation_policy_versions')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('status', 20)->default('draft');
            $this->jsonColumn($table, 'allocation_rules');
            $table->decimal('control_ratio', 5, 2)->default(0.10);
            $this->jsonColumn($table, 'variant_definitions');
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('ended_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['recovery_automation_policy_version_id', 'status']);
        });

        Schema::connection($this->connection)->create('recovery_automation_journeys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('caso_recuperacao_receita_id')->constrained('casos_recuperacao_receita')->cascadeOnDelete();
            $table->foreignId('recovery_automation_policy_version_id')->constrained('recovery_automation_policy_versions')->restrictOnDelete();
            $table->foreignId('recovery_automation_experiment_id')->nullable()->constrained('recovery_automation_experiments')->nullOnDelete();
            $table->string('variant_key', 60)->nullable();
            $table->string('journey_status', 20)->default('pending');
            $table->string('current_stage', 60)->nullable();
            $table->string('current_channel', 60)->nullable();
            $table->timestampTz('last_dispatched_at')->nullable();
            $table->timestampTz('next_evaluation_at')->nullable();
            $table->timestampTz('suppressed_until')->nullable();
            $table->timestampTz('rollback_marked_at')->nullable();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['caso_recuperacao_receita_id', 'journey_status']);
            $table->index(['recovery_automation_policy_version_id', 'journey_status']);
        });

        Schema::connection($this->connection)->create('recovery_automation_dispatches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recovery_automation_journey_id')->constrained('recovery_automation_journeys')->cascadeOnDelete();
            $table->foreignId('acao_recuperacao_receita_id')->nullable()->constrained('acoes_recuperacao_receita')->nullOnDelete();
            $table->string('dispatch_key', 180);
            $table->string('stage_key', 60);
            $table->string('channel', 60);
            $table->string('template_key', 120)->nullable();
            $table->unsignedSmallInteger('attempt_number')->default(1);
            $table->string('dispatch_status', 20)->default('scheduled');
            $table->string('fallback_reason', 80)->nullable();
            $table->timestampTz('scheduled_for')->nullable();
            $table->timestampTz('dispatched_at')->nullable();
            $this->jsonColumn($table, 'result_payload', nullable: true);
            $table->foreignId('operator_id')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->unique(['recovery_automation_journey_id', 'dispatch_key']);
            $table->index(['dispatch_status', 'scheduled_for']);
        });

        Schema::connection($this->connection)->create('recovery_automation_violations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recovery_automation_policy_version_id')->constrained('recovery_automation_policy_versions')->cascadeOnDelete();
            $table->foreignId('recovery_automation_journey_id')->nullable()->constrained('recovery_automation_journeys')->nullOnDelete();
            $table->foreignId('recovery_automation_dispatch_id')->nullable()->constrained('recovery_automation_dispatches')->nullOnDelete();
            $table->string('violation_type', 60);
            $table->string('severity', 20)->default('medium');
            $table->timestampTz('detected_at');
            $table->timestampTz('resolved_at')->nullable();
            $table->string('resolution_status', 20)->default('open');
            $table->text('summary');
            $this->jsonColumn($table, 'evidence_payload', nullable: true);
            $table->foreignId('resolved_by')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->timestampsTz();

            $table->index(['severity', 'resolution_status']);
            $table->index(['recovery_automation_policy_version_id', 'detected_at']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table recovery_automation_policy_versions add constraint recovery_automation_policy_versions_status_check check (status in ('draft', 'active', 'superseded', 'rolled_back'))");
            DB::connection($this->connection)->statement("alter table recovery_automation_experiments add constraint recovery_automation_experiments_status_check check (status in ('draft', 'running', 'completed', 'cancelled'))");
            DB::connection($this->connection)->statement("alter table recovery_automation_journeys add constraint recovery_automation_journeys_status_check check (journey_status in ('pending', 'active', 'paused', 'completed', 'rolled_back'))");
            DB::connection($this->connection)->statement("alter table recovery_automation_dispatches add constraint recovery_automation_dispatches_status_check check (dispatch_status in ('scheduled', 'dispatched', 'failed', 'suppressed', 'cancelled', 'replayed'))");
            DB::connection($this->connection)->statement("alter table recovery_automation_violations add constraint recovery_automation_violations_severity_check check (severity in ('low', 'medium', 'high', 'critical'))");
            DB::connection($this->connection)->statement("alter table recovery_automation_violations add constraint recovery_automation_violations_resolution_status_check check (resolution_status in ('open', 'acknowledged', 'resolved', 'rolled_back'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('recovery_automation_violations');
        Schema::connection($this->connection)->dropIfExists('recovery_automation_dispatches');
        Schema::connection($this->connection)->dropIfExists('recovery_automation_journeys');
        Schema::connection($this->connection)->dropIfExists('recovery_automation_experiments');
        Schema::connection($this->connection)->dropIfExists('recovery_automation_policy_versions');
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
