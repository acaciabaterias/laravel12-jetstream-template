<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection($this->connection)->create('politicas_recuperacao_receita', function (Blueprint $table): void {
            $table->id();
            $table->string('nome', 120);
            $table->string('slug', 80)->unique();
            $table->string('status', 20)->default('draft');
            $this->jsonColumn($table, 'entry_conditions');
            $this->jsonColumn($table, 'stage_definitions');
            $this->jsonColumn($table, 'escalation_rules');
            $this->jsonColumn($table, 'reengagement_rules');
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index('status');
        });

        Schema::connection($this->connection)->create('casos_recuperacao_receita', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('assinatura_id')->nullable()->constrained('assinaturas')->nullOnDelete();
            $table->foreignId('fatura_saas_id')->nullable()->constrained('faturas')->nullOnDelete();
            $table->foreignId('politica_recuperacao_receita_id')->constrained('politicas_recuperacao_receita')->restrictOnDelete();
            $table->string('status', 20)->default('open');
            $table->string('entry_reason', 40);
            $table->string('current_stage', 60);
            $table->string('severity', 20)->default('medium');
            $table->timestampTz('opened_at');
            $table->timestampTz('closed_at')->nullable();
            $table->foreignId('owner_user_id')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->timestampTz('last_action_at')->nullable();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['cliente_id', 'status']);
            $table->index(['fatura_saas_id', 'status']);
            $table->index(['status', 'severity']);
        });

        Schema::connection($this->connection)->create('acoes_recuperacao_receita', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('caso_recuperacao_receita_id')->constrained('casos_recuperacao_receita')->cascadeOnDelete();
            $table->string('action_type', 30);
            $table->string('channel', 30);
            $table->string('stage_name', 60);
            $table->string('status', 20)->default('scheduled');
            $table->string('idempotency_key', 180);
            $table->timestampTz('scheduled_for')->nullable();
            $table->timestampTz('executed_at')->nullable();
            $table->string('result_code', 60)->nullable();
            $table->foreignId('operator_user_id')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $this->jsonColumn($table, 'payload_snapshot', nullable: true);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->unique(['caso_recuperacao_receita_id', 'idempotency_key']);
            $table->index(['status', 'scheduled_for']);
            $table->index(['stage_name', 'channel']);
        });

        Schema::connection($this->connection)->create('compromissos_pagamento', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('caso_recuperacao_receita_id')->constrained('casos_recuperacao_receita')->cascadeOnDelete();
            $table->decimal('promised_amount', 12, 2)->nullable();
            $table->date('promised_date');
            $table->string('status', 20)->default('open');
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestampTz('suspends_until')->nullable();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['status', 'promised_date']);
        });

        Schema::connection($this->connection)->create('indicadores_recuperacao_receita', function (Blueprint $table): void {
            $table->id();
            $table->date('reference_date');
            $table->string('channel', 30)->nullable();
            $table->string('stage_name', 60)->nullable();
            $table->unsignedInteger('open_cases')->default(0);
            $table->unsignedInteger('escalated_cases')->default(0);
            $table->unsignedInteger('recovered_cases')->default(0);
            $table->unsignedInteger('broken_promises')->default(0);
            $table->decimal('recovery_amount', 12, 2)->default(0);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['reference_date', 'channel']);
            $table->index(['reference_date', 'stage_name']);
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table politicas_recuperacao_receita add constraint politicas_recuperacao_receita_status_check check (status in ('draft', 'active', 'inactive'))");
            DB::connection($this->connection)->statement("alter table casos_recuperacao_receita add constraint casos_recuperacao_receita_status_check check (status in ('open', 'paused', 'escalated', 'recovered', 'closed', 'cancelled'))");
            DB::connection($this->connection)->statement("alter table casos_recuperacao_receita add constraint casos_recuperacao_receita_severity_check check (severity in ('low', 'medium', 'high', 'critical'))");
            DB::connection($this->connection)->statement("alter table acoes_recuperacao_receita add constraint acoes_recuperacao_receita_action_type_check check (action_type in ('automated_reminder', 'manual_follow_up', 'escalation', 'promise_follow_up', 'reengagement', 'replay'))");
            DB::connection($this->connection)->statement("alter table acoes_recuperacao_receita add constraint acoes_recuperacao_receita_status_check check (status in ('scheduled', 'processing', 'sent', 'completed', 'failed', 'cancelled', 'skipped'))");
            DB::connection($this->connection)->statement("alter table compromissos_pagamento add constraint compromissos_pagamento_status_check check (status in ('open', 'honored', 'broken', 'cancelled'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('indicadores_recuperacao_receita');
        Schema::connection($this->connection)->dropIfExists('compromissos_pagamento');
        Schema::connection($this->connection)->dropIfExists('acoes_recuperacao_receita');
        Schema::connection($this->connection)->dropIfExists('casos_recuperacao_receita');
        Schema::connection($this->connection)->dropIfExists('politicas_recuperacao_receita');
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
