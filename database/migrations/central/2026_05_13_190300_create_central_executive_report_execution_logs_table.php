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
        if (Schema::connection($this->connection)->hasTable('executive_report_execution_logs')) {
            return;
        }

        Schema::connection($this->connection)->create('executive_report_execution_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('executive_report_export_id')->constrained('executive_report_exports')->cascadeOnDelete();
            $table->string('event_type', 40);
            $table->string('operator_name', 120)->nullable();
            $table->unsignedBigInteger('operator_id')->nullable();
            $this->jsonColumn($table, 'event_payload');
            $table->timestampTz('logged_at');
            $table->timestampsTz();

            $table->index(['event_type', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('executive_report_execution_logs');
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
