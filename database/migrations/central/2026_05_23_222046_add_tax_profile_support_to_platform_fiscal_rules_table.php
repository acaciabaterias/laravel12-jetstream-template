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
        Schema::connection($this->connection)->create('fiscal_tax_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('fiscal_rule_mapping_id')->constrained('fiscal_rule_mappings')->cascadeOnDelete();
            $table->foreignId('fiscal_rule_publication_record_id')->constrained('fiscal_rule_publication_records')->cascadeOnDelete();
            $table->string('scenario_key', 80);
            $table->string('cfop_code', 4);
            $table->string('ncm_code', 10)->nullable();
            $table->string('tax_regime', 40);
            $table->string('cst_code', 4)->nullable();
            $table->string('csosn_code', 4)->nullable();
            $table->string('partner_type', 40)->nullable();
            $table->string('operation_purpose', 40)->nullable();
            $table->string('origin_state', 2)->nullable();
            $table->string('destination_state', 2)->nullable();
            $table->decimal('interstate_tax_rate', 5, 2)->nullable();
            $this->jsonColumn($table, 'tax_payload');
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->unique('fiscal_rule_mapping_id');
            $table->index(['scenario_key', 'tax_regime']);
            $table->index(['origin_state', 'destination_state']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table fiscal_tax_profiles add constraint fiscal_tax_profiles_tax_regime_check check (tax_regime in ('regular', 'simple_national'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('fiscal_tax_profiles');
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
                $definition->default(DB::raw("'{}'::jsonb"));
            }

            return;
        }

        $definition = $table->json($column);

        if ($nullable) {
            $definition->nullable();
        }
    }
};
