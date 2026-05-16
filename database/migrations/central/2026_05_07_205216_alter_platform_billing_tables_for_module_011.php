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
        Schema::connection($this->connection)->table('planos', function (Blueprint $table) {
            $table->string('periodicidade', 30)->default('mensal');
            $table->decimal('preco_anual', 12, 2)->nullable();
            $table->json('beneficios')->nullable();
        });

        Schema::connection($this->connection)->create('politicas_inadimplencia', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->unsignedInteger('grace_period_days')->default(0);
            $table->unsignedInteger('block_after_days')->default(0);
            $table->string('reactivation_mode', 30)->default('automatic');
            $table->json('notification_profile')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestampsTz();

            $table->index('status');
        });

        Schema::connection($this->connection)->table('assinaturas', function (Blueprint $table) {
            $table->foreignId('politica_inadimplencia_id')
                ->nullable()
                ->constrained('politicas_inadimplencia')
                ->nullOnDelete();
            $table->date('grace_ends_at')->nullable();
            $table->timestampTz('blocked_at')->nullable();
            $table->string('blocked_reason', 255)->nullable();
            $table->timestampTz('reactivated_at')->nullable();
            $table->string('cancel_reason', 255)->nullable();
            $table->json('metadata')->nullable();

            $table->index('politica_inadimplencia_id');
            $table->index('grace_ends_at');
        });

        Schema::connection($this->connection)->table('faturas', function (Blueprint $table) {
            $table->string('billing_channel', 30)->nullable();
            $table->json('metadata')->nullable();
        });

        Schema::connection($this->connection)->create('eventos_comerciais_assinante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('assinatura_id')->constrained('assinaturas')->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('usuarios_plataforma')->nullOnDelete();
            $table->string('event_type', 50);
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->timestampTz('effective_at');
            $table->string('reason', 255)->nullable();
            $table->json('metadata')->nullable();
            $table->timestampsTz();

            $table->index(['cliente_id', 'event_type']);
            $table->index('effective_at');
        });

        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement("alter table politicas_inadimplencia add constraint politicas_inadimplencia_status_check check (status in ('active', 'inactive'))");
            DB::connection($this->connection)->statement('alter table assinaturas drop constraint if exists assinaturas_status_check');
            DB::connection($this->connection)->statement("alter table assinaturas add constraint assinaturas_status_check check (status in ('trial', 'active', 'expired', 'cancelled', 'past_due', 'paused', 'grace_period', 'blocked'))");
            DB::connection($this->connection)->statement('alter table faturas drop constraint if exists faturas_status_check');
            DB::connection($this->connection)->statement("alter table faturas add constraint faturas_status_check check (status in ('pending', 'paid', 'overdue', 'cancelled', 'refunded', 'written_off'))");
        }
    }

    public function down(): void
    {
        if ($this->usesPostgres()) {
            DB::connection($this->connection)->statement('alter table assinaturas drop constraint if exists assinaturas_status_check');
            DB::connection($this->connection)->statement("alter table assinaturas add constraint assinaturas_status_check check (status in ('trial', 'active', 'expired', 'cancelled', 'past_due', 'paused'))");
            DB::connection($this->connection)->statement('alter table faturas drop constraint if exists faturas_status_check');
            DB::connection($this->connection)->statement("alter table faturas add constraint faturas_status_check check (status in ('pending', 'paid', 'overdue', 'cancelled', 'refunded'))");
        }

        Schema::connection($this->connection)->dropIfExists('eventos_comerciais_assinante');

        Schema::connection($this->connection)->table('faturas', function (Blueprint $table) {
            $table->dropColumn([
                'billing_channel',
                'metadata',
            ]);
        });

        Schema::connection($this->connection)->table('assinaturas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('politica_inadimplencia_id');
            $table->dropColumn([
                'grace_ends_at',
                'blocked_at',
                'blocked_reason',
                'reactivated_at',
                'cancel_reason',
                'metadata',
            ]);
        });

        Schema::connection($this->connection)->dropIfExists('politicas_inadimplencia');

        Schema::connection($this->connection)->table('planos', function (Blueprint $table) {
            $table->dropColumn([
                'periodicidade',
                'preco_anual',
                'beneficios',
            ]);
        });
    }

    private function usesPostgres(): bool
    {
        return DB::connection($this->connection)->getDriverName() === 'pgsql';
    }
};
