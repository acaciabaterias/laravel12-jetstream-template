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
        Schema::connection($this->connection)->create('metric_channel_performance', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('snapshot_analytics_comercial_id')->constrained('snapshots_analytics_comercial')->cascadeOnDelete();
            $table->string('channel_type', 20);
            $table->string('channel_name', 40);
            $table->unsignedInteger('total_cases')->default(0);
            $table->unsignedInteger('successful_cases')->default(0);
            $table->unsignedInteger('failed_cases')->default(0);
            $table->decimal('recovered_amount', 12, 2)->default(0);
            $table->decimal('conversion_rate', 8, 4)->default(0);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['snapshot_analytics_comercial_id', 'channel_type']);
            $table->index('channel_name');
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table metric_channel_performance add constraint metric_channel_performance_channel_type_check check (channel_type in ('billing', 'recovery'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('metric_channel_performance');
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
