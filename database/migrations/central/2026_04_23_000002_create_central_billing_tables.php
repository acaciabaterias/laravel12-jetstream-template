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
        if (! Schema::connection($this->connection)->hasTable('assinaturas')) {
            Schema::connection($this->connection)->create('assinaturas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
                $table->foreignId('plano_id')->constrained('planos')->restrictOnDelete();
                $table->string('status', 30);
                $table->date('data_inicio');
                $table->date('data_proximo_ciclo');
                $table->date('data_termino')->nullable();
                $table->string('stripe_subscription_id', 150)->nullable();
                $table->string('stripe_customer_id', 150)->nullable();
                $table->text('observacoes')->nullable();
                $table->timestampsTz();

                $table->index('cliente_id');
                $table->index('status');
            });
        } elseif (! Schema::connection($this->connection)->hasColumn('assinaturas', 'observacoes')) {
            Schema::connection($this->connection)->table('assinaturas', function (Blueprint $table) {
                $table->text('observacoes')->nullable();
            });
        }

        if (! Schema::connection($this->connection)->hasTable('faturas')) {
            Schema::connection($this->connection)->create('faturas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assinatura_id')->constrained('assinaturas')->cascadeOnDelete();
                $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
                $table->string('referencia', 30);
                $table->date('vencimento');
                $table->decimal('valor', 12, 2);
                $table->decimal('valor_pago', 12, 2)->nullable();
                $table->string('status', 30)->default('pending');
                $table->string('external_invoice_id', 150)->nullable();
                $table->timestampTz('paid_at')->nullable();
                if ($this->usesPostgres()) {
                    $table->jsonb('payload_gateway')->default(DB::raw("'{}'::jsonb"));
                } else {
                    $table->json('payload_gateway')->nullable();
                }
                $table->timestampsTz();

                $table->unique(['assinatura_id', 'referencia']);
                $table->index('cliente_id');
                $table->index('status');
                $table->index('vencimento');
            });
        }

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement('alter table assinaturas drop constraint if exists assinaturas_status_check');
            DB::connection($this->connection)->statement(
                "alter table assinaturas add constraint assinaturas_status_check check (status in ('trial', 'active', 'expired', 'cancelled', 'past_due', 'paused'))"
            );
            DB::connection($this->connection)->statement('alter table faturas drop constraint if exists faturas_status_check');
            DB::connection($this->connection)->statement(
                "alter table faturas add constraint faturas_status_check check (status in ('pending', 'paid', 'overdue', 'cancelled', 'refunded'))"
            );
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('faturas');
        Schema::connection($this->connection)->dropIfExists('assinaturas');
    }

    private function usesPostgres(): bool
    {
        return DB::connection($this->connection)->getDriverName() === 'pgsql';
    }
};
