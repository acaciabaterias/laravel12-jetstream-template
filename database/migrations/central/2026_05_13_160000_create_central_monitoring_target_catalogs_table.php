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
        Schema::connection($this->connection)->create('monitoring_target_catalogs', function (Blueprint $table): void {
            $table->id();
            $table->string('flow_name', 80);
            $table->string('target_name', 120);
            $table->string('environment', 40);
            $table->string('endpoint', 255);
            $table->string('collector_type', 40);
            $table->string('status', 20)->default('healthy');
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->unique(['environment', 'target_name']);
            $table->index(['flow_name', 'status']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table monitoring_target_catalogs add constraint monitoring_target_catalogs_status_check check (status in ('healthy', 'degraded', 'unavailable'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('monitoring_target_catalogs');
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
