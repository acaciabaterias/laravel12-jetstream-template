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
        Schema::connection($this->connection)->create('performance_rollback_evidences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tuning_change_record_id')->constrained('tuning_change_records')->cascadeOnDelete();
            $table->foreignId('operator_user_id')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->timestampTz('recorded_at');
            $table->string('result_status', 20)->default('pending');
            $table->text('rollback_reason');
            $this->jsonColumn($table, 'payload', nullable: true);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['result_status', 'recorded_at']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table performance_rollback_evidences add constraint performance_rollback_evidences_result_status_check check (result_status in ('pending', 'success', 'partial', 'failed'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('performance_rollback_evidences');
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
