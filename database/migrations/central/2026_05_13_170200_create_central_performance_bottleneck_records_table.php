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
        Schema::connection($this->connection)->create('performance_bottleneck_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('benchmark_execution_record_id')->constrained('benchmark_execution_records')->cascadeOnDelete();
            $table->string('flow_name', 80);
            $table->string('category', 30);
            $table->string('component_name', 120);
            $table->text('summary');
            $table->string('impact_level', 20)->default('warning');
            $this->jsonColumn($table, 'evidence_payload', nullable: true);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['flow_name', 'category']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table performance_bottleneck_records add constraint performance_bottleneck_records_category_check check (category in ('database', 'queue', 'external_endpoint', 'application'))");
            DB::connection($this->connection)->statement("alter table performance_bottleneck_records add constraint performance_bottleneck_records_impact_level_check check (impact_level in ('warning', 'critical'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('performance_bottleneck_records');
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
