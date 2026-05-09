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
        Schema::connection($this->connection)->create('recortes_coorte_comercial', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('snapshot_analytics_comercial_id')->constrained('snapshots_analytics_comercial')->cascadeOnDelete();
            $table->string('cohort_label', 80);
            $table->date('cohort_start_date');
            $table->date('cohort_end_date')->nullable();
            $table->unsignedInteger('active_subscriptions')->default(0);
            $table->unsignedInteger('cancelled_subscriptions')->default(0);
            $table->unsignedInteger('recovered_subscriptions')->default(0);
            $table->unsignedInteger('delinquent_subscriptions')->default(0);
            $table->decimal('mrr_amount', 12, 2)->default(0);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['snapshot_analytics_comercial_id', 'cohort_label']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('recortes_coorte_comercial');
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
