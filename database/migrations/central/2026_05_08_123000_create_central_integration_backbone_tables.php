<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    public function up(): void
    {
        Schema::connection($this->connection)->create('evento_outboxes', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 120);
            $table->string('event_version', 20)->default('v1');
            $table->string('tenant_external_ref', 120);
            $table->uuid('correlation_id');
            $table->uuid('causation_id')->nullable();
            $table->string('idempotency_key', 180)->unique();
            $table->string('origin_context', 120)->nullable();
            $table->string('status', 30)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestampTz('occurred_at');
            $table->timestampTz('available_at')->nullable();
            $table->timestampTz('dispatched_at')->nullable();
            $table->text('last_error')->nullable();
            $table->json('payload');
            $table->json('metadata')->nullable();
            $table->timestampsTz();

            $table->index(['tenant_external_ref', 'status']);
            $table->index(['event_type', 'event_version']);
            $table->index('correlation_id');
        });

        Schema::connection($this->connection)->create('entregas_integracao', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('entregavel');
            $table->string('direction', 20);
            $table->string('transport_kind', 30);
            $table->string('target', 180);
            $table->string('status', 30)->default('pending');
            $table->unsignedInteger('attempt_number')->default(1);
            $table->unsignedInteger('latency_ms')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->foreignId('replayed_from_entrega_id')->nullable()->constrained('entregas_integracao')->nullOnDelete();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestampsTz();

            $table->index(['direction', 'status']);
            $table->index('target');
        });

        Schema::connection($this->connection)->create('contratos_evento', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 120);
            $table->string('event_version', 20);
            $table->string('producer', 120);
            $table->string('status', 30)->default('active');
            $table->json('consumers');
            $table->json('schema_definition')->nullable();
            $table->text('compatibility_notes')->nullable();
            $table->timestampTz('deprecated_at')->nullable();
            $table->timestampsTz();

            $table->unique(['event_type', 'event_version']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('contratos_evento');
        Schema::connection($this->connection)->dropIfExists('entregas_integracao');
        Schema::connection($this->connection)->dropIfExists('evento_outboxes');
    }
};
