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
        Schema::connection($this->connection)->create('load_test_baselines', function (Blueprint $table): void {
            $table->id();
            $table->string('scenario_name', 120);
            $table->string('flow_name', 80);
            $table->unsignedInteger('throughput_per_minute')->default(0);
            $table->unsignedInteger('p95_latency_ms')->default(0);
            $table->decimal('error_rate', 8, 4)->default(0);
            $table->text('environment_notes')->nullable();
            $table->timestampTz('accepted_at')->nullable();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['scenario_name', 'flow_name']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('load_test_baselines');
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
