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
        Schema::connection($this->connection)->create('tuning_change_records', function (Blueprint $table): void {
            $table->id();
            $table->string('flow_name', 80);
            $table->string('environment', 40);
            $table->string('change_key', 120)->unique();
            $table->text('hypothesis_summary');
            $table->string('change_type', 60);
            $table->timestampTz('applied_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('baseline_execution_id')->nullable()->constrained('benchmark_execution_records')->nullOnDelete();
            $table->foreignId('validation_execution_id')->nullable()->constrained('benchmark_execution_records')->nullOnDelete();
            $table->boolean('rollback_recommended')->default(false);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['flow_name', 'environment']);
            $table->index(['status', 'rollback_recommended']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table tuning_change_records add constraint tuning_change_records_status_check check (status in ('pending', 'validated', 'promoted', 'rolled_back'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('tuning_change_records');
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
