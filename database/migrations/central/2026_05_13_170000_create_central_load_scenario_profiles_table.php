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
        Schema::connection($this->connection)->create('load_scenario_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('flow_name', 80);
            $table->string('scenario_name', 120);
            $table->string('environment', 40);
            $table->unsignedInteger('request_budget');
            $table->unsignedInteger('duration_seconds');
            $table->unsignedInteger('concurrency_level');
            $table->unsignedInteger('expected_throughput_per_minute');
            $table->unsignedInteger('expected_p95_latency_ms');
            $table->decimal('expected_error_rate', 8, 4)->default(0);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->unique(['flow_name', 'scenario_name', 'environment']);
            $table->index(['flow_name', 'environment']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('load_scenario_profiles');
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
