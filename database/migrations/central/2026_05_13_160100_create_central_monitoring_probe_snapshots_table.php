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
        Schema::connection($this->connection)->create('monitoring_probe_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('monitoring_target_catalog_id')->constrained('monitoring_target_catalogs')->cascadeOnDelete();
            $table->timestampTz('reference_at');
            $table->string('scrape_status', 20)->default('healthy');
            $table->unsignedInteger('latency_ms')->default(0);
            $table->unsignedInteger('sample_count')->default(0);
            $table->text('failure_reason')->nullable();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['monitoring_target_catalog_id', 'scrape_status']);
            $table->index('reference_at');
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table monitoring_probe_snapshots add constraint monitoring_probe_snapshots_scrape_status_check check (scrape_status in ('healthy', 'degraded', 'unavailable'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('monitoring_probe_snapshots');
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
