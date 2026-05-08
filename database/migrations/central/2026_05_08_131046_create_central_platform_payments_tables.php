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
        Schema::connection($this->connection)->create('gateways_cobranca_saas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120);
            $table->string('slug', 80)->unique();
            $table->string('driver', 60);
            $table->string('status', 20)->default('active');
            $this->jsonColumn($table, 'supported_channels');
            $this->jsonColumn($table, 'credential_profile');
            $table->unsignedInteger('timeout_seconds')->default(30);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index('status');
        });

        Schema::connection($this->connection)->create('cobrancas_saas_externas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fatura_saas_id')->constrained('faturas')->cascadeOnDelete();
            $table->foreignId('gateway_cobranca_saas_id')->constrained('gateways_cobranca_saas')->restrictOnDelete();
            $table->string('external_charge_id', 150)->nullable();
            $table->string('external_reference', 120);
            $table->string('payment_channel', 30);
            $table->string('status', 30)->default('draft');
            $table->decimal('valor_emitido', 12, 2);
            $table->date('vencimento_emitido');
            $table->timestampTz('issued_at')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->string('failure_reason', 255)->nullable();
            $table->string('idempotency_key', 160);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->unique(['gateway_cobranca_saas_id', 'external_charge_id']);
            $table->unique(['fatura_saas_id', 'idempotency_key']);
            $table->index(['fatura_saas_id', 'status']);
            $table->index('external_reference');
        });

        Schema::connection($this->connection)->create('retornos_pagamento_saas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gateway_cobranca_saas_id')->constrained('gateways_cobranca_saas')->restrictOnDelete();
            $table->foreignId('cobranca_saas_externa_id')->nullable()->constrained('cobrancas_saas_externas')->nullOnDelete();
            $table->string('source_type', 30);
            $table->string('external_event_id', 180)->nullable();
            $table->string('external_reference', 120)->nullable();
            $table->string('event_type', 80);
            $this->jsonColumn($table, 'payload');
            $table->timestampTz('received_at');
            $table->timestampTz('processed_at')->nullable();
            $table->string('processing_status', 20)->default('pending');
            $table->string('processing_error', 255)->nullable();
            $table->string('idempotency_key', 180);
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->unique(['gateway_cobranca_saas_id', 'idempotency_key']);
            $table->index(['processing_status', 'received_at']);
            $table->index('external_reference');
        });

        Schema::connection($this->connection)->create('conciliacoes_pagamento_saas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fatura_saas_id')->constrained('faturas')->cascadeOnDelete();
            $table->foreignId('cobranca_saas_externa_id')->constrained('cobrancas_saas_externas')->cascadeOnDelete();
            $table->foreignId('retorno_pagamento_saas_id')->nullable()->constrained('retornos_pagamento_saas')->nullOnDelete();
            $table->string('status', 30)->default('matched');
            $table->string('reconciliation_type', 30)->default('automatic');
            $table->decimal('expected_amount', 12, 2);
            $table->decimal('received_amount', 12, 2)->nullable();
            $table->decimal('difference_amount', 12, 2)->nullable();
            $table->timestampTz('reconciled_at')->nullable();
            $table->foreignId('operator_user_id')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->text('notes')->nullable();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['fatura_saas_id', 'status']);
            $table->index('reconciled_at');
        });

        Schema::connection($this->connection)->create('excecoes_conciliacao_saas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fatura_saas_id')->constrained('faturas')->cascadeOnDelete();
            $table->foreignId('cobranca_saas_externa_id')->nullable()->constrained('cobrancas_saas_externas')->nullOnDelete();
            $table->foreignId('retorno_pagamento_saas_id')->nullable()->constrained('retornos_pagamento_saas')->nullOnDelete();
            $table->foreignId('conciliacao_pagamento_saas_id')->nullable()->constrained('conciliacoes_pagamento_saas')->nullOnDelete();
            $table->string('status', 20)->default('open');
            $table->string('exception_type', 40);
            $table->string('severity', 20)->default('medium');
            $table->string('impact_on_subscription', 30)->default('none');
            $table->timestampTz('opened_at');
            $table->timestampTz('resolved_at')->nullable();
            $table->foreignId('owner_user_id')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $this->jsonColumn($table, 'metadata', nullable: true);
            $table->timestampsTz();

            $table->index(['status', 'severity']);
            $table->index('opened_at');
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table gateways_cobranca_saas add constraint gateways_cobranca_saas_status_check check (status in ('active', 'inactive', 'degraded'))");
            DB::connection($this->connection)->statement("alter table cobrancas_saas_externas add constraint cobrancas_saas_externas_status_check check (status in ('draft', 'submitted', 'pending', 'paid', 'expired', 'cancelled', 'failed', 'refunded', 'chargeback'))");
            DB::connection($this->connection)->statement("alter table retornos_pagamento_saas add constraint retornos_pagamento_saas_processing_status_check check (processing_status in ('pending', 'processed', 'ignored', 'failed'))");
            DB::connection($this->connection)->statement("alter table conciliacoes_pagamento_saas add constraint conciliacoes_pagamento_saas_status_check check (status in ('matched', 'partially_matched', 'exception', 'replayed', 'reversed'))");
            DB::connection($this->connection)->statement("alter table excecoes_conciliacao_saas add constraint excecoes_conciliacao_saas_status_check check (status in ('open', 'investigating', 'resolved', 'dismissed'))");
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('excecoes_conciliacao_saas');
        Schema::connection($this->connection)->dropIfExists('conciliacoes_pagamento_saas');
        Schema::connection($this->connection)->dropIfExists('retornos_pagamento_saas');
        Schema::connection($this->connection)->dropIfExists('cobrancas_saas_externas');
        Schema::connection($this->connection)->dropIfExists('gateways_cobranca_saas');
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
