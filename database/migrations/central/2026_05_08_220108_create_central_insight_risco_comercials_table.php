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
        Schema::connection($this->connection)->create('insights_risco_comercial', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('snapshot_analytics_comercial_id')->constrained('snapshots_analytics_comercial')->cascadeOnDelete();
            $table->string('risk_type', 30);
            $table->string('severity', 20)->default('medium');
            $table->unsignedInteger('total_accounts')->default(0);
            $table->decimal('total_exposure', 12, 2)->default(0);
            $table->text('description');
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['snapshot_analytics_comercial_id', 'risk_type']);
            $table->index(['severity', 'total_accounts']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table insights_risco_comercial add constraint insights_risco_comercial_risk_type_check check (risk_type in ('churn', 'delinquency', 'recovery_stall', 'payment_failure'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('insights_risco_comercial');
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
