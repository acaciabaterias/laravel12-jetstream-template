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
        Schema::connection($this->connection)->create('alert_rule_definitions', function (Blueprint $table): void {
            $table->id();
            $table->string('flow_name', 80);
            $table->string('rule_name', 120);
            $table->string('severity', 20)->default('warning');
            $table->string('version', 40);
            $table->text('condition_summary');
            $table->string('status', 20)->default('pending');
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->unique(['flow_name', 'rule_name', 'version']);
            $table->index(['flow_name', 'status']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table alert_rule_definitions add constraint alert_rule_definitions_severity_check check (severity in ('healthy', 'warning', 'critical'))");
            DB::connection($this->connection)->statement("alter table alert_rule_definitions add constraint alert_rule_definitions_status_check check (status in ('pending', 'applied', 'failed', 'rolled_back'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('alert_rule_definitions');
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
