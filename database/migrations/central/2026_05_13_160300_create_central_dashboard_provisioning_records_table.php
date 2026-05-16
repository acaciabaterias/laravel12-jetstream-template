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
        Schema::connection($this->connection)->create('dashboard_provisioning_records', function (Blueprint $table): void {
            $table->id();
            $table->string('package_name', 120);
            $table->string('version', 40);
            $table->string('environment', 40);
            $table->timestampTz('applied_at')->nullable();
            $table->timestampTz('validated_at')->nullable();
            $table->string('rollback_version', 40)->nullable();
            $table->string('status', 20)->default('pending');
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->unique(['package_name', 'version', 'environment']);
            $table->index(['environment', 'status']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table dashboard_provisioning_records add constraint dashboard_provisioning_records_status_check check (status in ('pending', 'applied', 'failed', 'rolled_back'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('dashboard_provisioning_records');
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
