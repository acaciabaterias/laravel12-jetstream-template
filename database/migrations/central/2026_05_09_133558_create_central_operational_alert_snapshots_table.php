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
        Schema::connection($this->connection)->create('operational_alert_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->timestampTz('reference_at');
            $table->string('flow_name', 80);
            $table->string('status', 20)->default('healthy');
            $table->string('severity', 20)->default('healthy');
            $table->unsignedInteger('backlog_count')->default(0);
            $table->unsignedInteger('latency_ms')->nullable();
            $table->decimal('failure_rate', 8, 4)->default(0);
            $table->unsignedInteger('open_replays')->default(0);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['flow_name', 'reference_at']);
            $table->index(['severity', 'status']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table operational_alert_snapshots add constraint operational_alert_snapshots_status_check check (status in ('healthy', 'degraded', 'unavailable'))");
            DB::connection($this->connection)->statement("alter table operational_alert_snapshots add constraint operational_alert_snapshots_severity_check check (severity in ('healthy', 'warning', 'critical'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('operational_alert_snapshots');
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
