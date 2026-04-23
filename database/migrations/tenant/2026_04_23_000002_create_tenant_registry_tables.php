<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection($this->connection)->create('fabricantes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('codigo', 60)->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::connection($this->connection)->create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fabricante_id')->constrained('fabricantes')->cascadeOnDelete();
            $table->string('modelo', 150);
            $table->string('motorizacao', 60)->nullable();
            $table->integer('ano_inicio')->nullable();
            $table->integer('ano_fim')->nullable();
            $table->jsonb('atributos_dinamicos')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['fabricante_id', 'modelo']);
        });

        Schema::connection($this->connection)->create('baterias', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 80)->unique();
            $table->string('marca', 120);
            $table->string('tecnologia', 60)->nullable();
            $table->integer('amperagem')->nullable();
            $table->string('polo', 20)->nullable();
            $table->decimal('preco_venda', 12, 2)->default(0);
            $table->jsonb('atributos_dinamicos')->nullable();
            $table->decimal('peso_sucata_kg', 10, 2)->nullable();
            $table->decimal('valor_base_sucata_kg', 10, 2)->nullable();
            $table->boolean('tem_logistica_reversa')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::connection($this->connection)->create('aplicacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
            $table->foreignId('bateria_id')->constrained('baterias')->cascadeOnDelete();
            $table->text('observacao')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['veiculo_id', 'bateria_id']);
        });

        DB::connection($this->connection)->statement(
            'create unique index if not exists idx_tenant_fabricantes_nome_unique on fabricantes(lower(nome)) where deleted_at is null'
        );
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('aplicacoes');
        Schema::connection($this->connection)->dropIfExists('baterias');
        Schema::connection($this->connection)->dropIfExists('veiculos');
        Schema::connection($this->connection)->dropIfExists('fabricantes');
    }
};
