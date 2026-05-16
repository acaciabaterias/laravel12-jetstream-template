<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection($this->connection)->create('rotas_entrega', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entregador_id')->constrained('users')->cascadeOnDelete();
            $table->date('data_rota');
            $table->string('status', 30)->default('planejada');
            $table->foreignId('veiculo_id')->nullable()->constrained('veiculos')->nullOnDelete();
            $table->text('observacoes')->nullable();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('pontos_entrega', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rota_entrega_id')->constrained('rotas_entrega')->cascadeOnDelete();
            $table->foreignId('vale_id')->nullable()->constrained('vales')->nullOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('endereco_entrega', 255);
            $table->integer('ordem_parada');
            $table->string('status', 30)->default('planejado');
            $table->decimal('peso_sucata_coletado', 10, 2)->nullable();
            $table->text('observacao')->nullable();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('recebimentos_moveis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ponto_entrega_id')->constrained('pontos_entrega')->cascadeOnDelete();
            $table->decimal('valor', 12, 2);
            $table->string('metodo_pagamento', 40);
            $table->boolean('status_sincronizado')->default(false);
            $table->text('comprovante_path')->nullable();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('geolocalizacao_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rota_entrega_id')->nullable()->constrained('rotas_entrega')->nullOnDelete();
            $table->foreignId('ponto_entrega_id')->nullable()->constrained('pontos_entrega')->nullOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('tipo_evento', 50);
            $table->timestampTz('recorded_at');
        });

        Schema::connection($this->connection)->create('sync_eventos', function (Blueprint $table) {
            $table->id();
            $table->uuid('dispositivo_uuid');
            $table->string('entidade_tipo', 50);
            $table->unsignedBigInteger('entidade_id')->nullable();
            $table->string('payload_hash', 120)->unique();
            $table->jsonb('payload');
            $table->string('status', 30)->default('pendente');
            $table->timestampTz('processed_at')->nullable();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('ordens_servico_garantia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('bateria_id')->constrained('baterias')->cascadeOnDelete();
            $table->foreignId('vale_original_id')->nullable()->constrained('vales')->nullOnDelete();
            $table->timestampTz('data_abertura');
            $table->string('status', 30)->default('aberta');
            $table->text('laudo')->nullable();
            $table->string('resultado', 30)->nullable();
            $table->decimal('cobranca_valor', 12, 2)->nullable();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('baterias_emprestimo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_garantia_id')->constrained('ordens_servico_garantia')->cascadeOnDelete();
            $table->foreignId('bateria_usada_id')->constrained('baterias')->cascadeOnDelete();
            $table->timestampTz('data_retirada');
            $table->timestampTz('data_devolucao_prevista');
            $table->timestampTz('data_devolucao_real')->nullable();
            $table->text('termo_arquivo_path')->nullable();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('notificacoes_whatsapp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_garantia_id')->constrained('ordens_servico_garantia')->cascadeOnDelete();
            $table->string('cliente_telefone', 30)->nullable();
            $table->string('status', 30)->default('pendente');
            $table->text('mensagem');
            $table->timestampTz('data_envio')->nullable();
            $table->string('identificador_externo', 150)->nullable();
            $table->text('tracking_error')->nullable();
            $table->timestampsTz();
        });

        Schema::connection($this->connection)->create('indices_retorno_produto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bateria_id')->constrained('baterias')->cascadeOnDelete();
            $table->date('periodo_inicio');
            $table->date('periodo_fim');
            $table->integer('total_vendidas')->default(0);
            $table->integer('total_garantias')->default(0);
            $table->decimal('indice_calculado', 8, 4)->default(0);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('indices_retorno_produto');
        Schema::connection($this->connection)->dropIfExists('notificacoes_whatsapp');
        Schema::connection($this->connection)->dropIfExists('baterias_emprestimo');
        Schema::connection($this->connection)->dropIfExists('ordens_servico_garantia');
        Schema::connection($this->connection)->dropIfExists('sync_eventos');
        Schema::connection($this->connection)->dropIfExists('geolocalizacao_eventos');
        Schema::connection($this->connection)->dropIfExists('recebimentos_moveis');
        Schema::connection($this->connection)->dropIfExists('pontos_entrega');
        Schema::connection($this->connection)->dropIfExists('rotas_entrega');
    }
};
