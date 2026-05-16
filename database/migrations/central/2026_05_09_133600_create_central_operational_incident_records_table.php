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
        Schema::connection($this->connection)->create('operational_incident_records', function (Blueprint $table): void {
            $table->id();
            $table->string('incident_key', 120)->unique();
            $table->string('flow_name', 80);
            $table->string('severity', 20)->default('warning');
            $table->string('status', 20)->default('open');
            $table->timestampTz('opened_at');
            $table->timestampTz('acknowledged_at')->nullable();
            $table->timestampTz('resolved_at')->nullable();
            $table->text('summary');
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['flow_name', 'status']);
            $table->index(['severity', 'opened_at']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table operational_incident_records add constraint operational_incident_records_severity_check check (severity in ('healthy', 'warning', 'critical'))");
            DB::connection($this->connection)->statement("alter table operational_incident_records add constraint operational_incident_records_status_check check (status in ('open', 'acknowledged', 'resolved', 'closed'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('operational_incident_records');
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
