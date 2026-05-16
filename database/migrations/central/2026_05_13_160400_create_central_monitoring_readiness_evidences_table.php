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
        Schema::connection($this->connection)->create('monitoring_readiness_evidences', function (Blueprint $table): void {
            $table->id();
            $table->string('environment', 40);
            $table->string('evidence_type', 60);
            $table->foreignId('operator_user_id')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->timestampTz('recorded_at');
            $table->string('result_status', 20)->default('pending');
            $this->jsonColumn($table, 'payload', nullable: true);
            $table->text('notes')->nullable();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['environment', 'result_status']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table monitoring_readiness_evidences add constraint monitoring_readiness_evidences_result_status_check check (result_status in ('pending', 'success', 'partial', 'failed'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('monitoring_readiness_evidences');
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
