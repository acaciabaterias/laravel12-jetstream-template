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
        if (Schema::connection($this->connection)->hasTable('executive_report_exports')) {
            return;
        }

        Schema::connection($this->connection)->create('executive_report_exports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('executive_analytics_snapshot_id')->constrained('executive_analytics_snapshots')->cascadeOnDelete();
            $table->foreignId('executive_report_definition_id')->constrained('executive_report_definitions')->cascadeOnDelete();
            $table->foreignId('reexecuted_from_export_id')->nullable()->constrained('executive_report_exports')->nullOnDelete();
            $table->string('format', 20);
            $table->text('file_reference')->nullable();
            $table->string('export_status', 20)->default('pending');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestampTz('requested_at');
            $table->timestampTz('completed_at')->nullable();
            $table->text('scope_summary')->nullable();
            $this->jsonColumn($table, 'metadata');
            $table->timestampsTz();

            $table->index(['format', 'export_status']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('executive_report_exports');
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
