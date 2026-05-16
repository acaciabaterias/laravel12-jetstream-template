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
        Schema::connection($this->connection)->create('benchmark_execution_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('load_scenario_profile_id')->constrained('load_scenario_profiles')->cascadeOnDelete();
            $table->timestampTz('started_at');
            $table->timestampTz('finished_at')->nullable();
            $table->unsignedInteger('throughput_per_minute')->default(0);
            $table->unsignedInteger('p95_latency_ms')->default(0);
            $table->decimal('error_rate', 8, 4)->default(0);
            $table->string('status', 20)->default('pending');
            $table->string('comparison_status', 20)->default('baseline');
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['load_scenario_profile_id', 'status']);
            $table->index(['comparison_status', 'created_at']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table benchmark_execution_records add constraint benchmark_execution_records_status_check check (status in ('pending', 'completed', 'incomplete'))");
            DB::connection($this->connection)->statement("alter table benchmark_execution_records add constraint benchmark_execution_records_comparison_status_check check (comparison_status in ('baseline', 'improved', 'stable', 'regressed'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('benchmark_execution_records');
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
                $definition->default(DB::raw("'[]'::jsonb"));
            }

            return;
        }

        $definition = $table->json($column);
        if ($nullable) {
            $definition->nullable();
        }
    }
};
