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
        Schema::connection($this->connection)->create('operational_slo_definitions', function (Blueprint $table): void {
            $table->id();
            $table->string('flow_name', 80);
            $table->string('metric_key', 80);
            $table->decimal('target_value', 12, 4)->default(0);
            $table->decimal('warning_threshold', 12, 4)->default(0);
            $table->decimal('critical_threshold', 12, 4)->default(0);
            $this->jsonColumn($table, 'severity_mapping', nullable: true);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->unique(['flow_name', 'metric_key']);
            $table->index('flow_name');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('operational_slo_definitions');
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
