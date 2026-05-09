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
        Schema::connection($this->connection)->create('drilldowns_analytics_comercial', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('snapshot_analytics_comercial_id')->constrained('snapshots_analytics_comercial')->cascadeOnDelete();
            $table->string('source_type', 60);
            $table->unsignedBigInteger('source_id');
            $table->string('dimension_type', 40);
            $table->string('dimension_value', 80)->nullable();
            $table->string('metric_key', 60);
            $table->decimal('metric_value', 12, 2)->default(0);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['snapshot_analytics_comercial_id', 'dimension_type']);
            $table->index(['source_type', 'source_id']);
            $table->index('metric_key');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('drilldowns_analytics_comercial');
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
