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
        Schema::connection($this->connection)->create('snapshots_analytics_comercial', function (Blueprint $table): void {
            $table->id();
            $table->string('snapshot_type', 30)->default('executive');
            $table->date('reference_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->string('rebuild_status', 20)->default('completed');
            $table->decimal('mrr_amount', 12, 2)->default(0);
            $table->unsignedInteger('churn_count')->default(0);
            $table->decimal('churn_rate', 8, 4)->default(0);
            $table->unsignedInteger('delinquent_count')->default(0);
            $table->unsignedInteger('recovered_count')->default(0);
            $table->decimal('recovered_amount', 12, 2)->default(0);
            $table->unsignedInteger('blocked_count')->default(0);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['snapshot_type', 'reference_date']);
            $table->index(['period_start', 'period_end']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table snapshots_analytics_comercial add constraint snapshots_analytics_comercial_snapshot_type_check check (snapshot_type in ('executive', 'cohort', 'channel', 'drilldown'))");
            DB::connection($this->connection)->statement("alter table snapshots_analytics_comercial add constraint snapshots_analytics_comercial_rebuild_status_check check (rebuild_status in ('pending', 'processing', 'completed', 'failed'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('snapshots_analytics_comercial');
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
