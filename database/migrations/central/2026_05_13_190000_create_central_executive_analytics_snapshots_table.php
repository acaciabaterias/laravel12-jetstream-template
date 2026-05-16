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
        if (Schema::connection($this->connection)->hasTable('executive_analytics_snapshots')) {
            return;
        }

        Schema::connection($this->connection)->create('executive_analytics_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('snapshot_key', 80)->default('executive-overview');
            $table->unsignedBigInteger('source_snapshot_analytics_comercial_id')->nullable();
            $table->date('reference_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->string('filter_hash', 64);
            $this->jsonColumn($table, 'filter_payload');
            $this->jsonColumn($table, 'kpi_payload');
            $this->jsonColumn($table, 'drilldown_payload');
            $table->string('snapshot_status', 20)->default('ready');
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestampsTz();

            $table->index(['snapshot_key', 'filter_hash']);
            $table->index(['reference_date', 'snapshot_status']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('executive_analytics_snapshots');
    }

    private function usesPostgres(): bool
    {
        return DB::connection($this->connection)->getDriverName() === 'pgsql';
    }

    private function jsonColumn(Blueprint $table, string $column): void
    {
        if ($this->usesPostgres()) {
            $table->jsonb($column)->default(DB::raw("'{}'::jsonb"));

            return;
        }

        $table->json($column)->nullable();
    }
};
