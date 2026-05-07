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
        $driver = Schema::connection($this->connection)->getConnection()->getDriverName();

        Schema::connection($this->connection)->create('certificados_digitais', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('nome_referencia', 120);
            $table->string('finalidade', 30)->default('fiscal');
            $table->string('modelo', 30)->default('a1');
            $table->string('formato', 20)->default('pfx');
            $table->text('conteudo_certificado')->nullable();
            $table->text('senha_certificado')->nullable();
            $table->string('serial_numero', 100)->nullable();
            $table->string('emissor', 150)->nullable();
            $table->string('titular_documento', 30)->nullable();
            $table->date('validade_inicio')->nullable();
            $table->date('validade_fim')->nullable();
            $table->string('status', 30)->default('active');
            $table->unsignedTinyInteger('prioridade')->default(10);
            $table->timestampTz('revoked_at')->nullable();
            if ($driver === 'pgsql') {
                $table->jsonb('metadata')->default(DB::raw("'{}'::jsonb"));
            } else {
                $table->json('metadata')->nullable();
            }
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['cliente_id', 'finalidade', 'status'], 'certificados_cliente_finalidade_status_idx');
            $table->index('validade_fim');
        });

        if ($driver === 'pgsql') {
            DB::connection($this->connection)->statement(
                "alter table certificados_digitais add constraint certificados_digitais_finalidade_check check (finalidade in ('fiscal', 'bancario', 'openfinance', 'geral'))"
            );
            DB::connection($this->connection)->statement(
                "alter table certificados_digitais add constraint certificados_digitais_modelo_check check (modelo in ('a1', 'a3', 'token', 'hsm', 'outro'))"
            );
            DB::connection($this->connection)->statement(
                "alter table certificados_digitais add constraint certificados_digitais_formato_check check (formato in ('pfx', 'p12', 'pem', 'cer', 'key', 'remote'))"
            );
            DB::connection($this->connection)->statement(
                "alter table certificados_digitais add constraint certificados_digitais_status_check check (status in ('active', 'inactive', 'expired', 'revoked'))"
            );
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('certificados_digitais');
    }
};
