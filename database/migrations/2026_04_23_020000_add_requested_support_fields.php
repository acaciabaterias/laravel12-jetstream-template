<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table): void {
            if (! Schema::hasColumn('clientes', 'endereco')) {
                $table->string('endereco')->nullable()->after('telefone');
            }

            if (! Schema::hasColumn('clientes', 'saldo_sucata_kg')) {
                $table->decimal('saldo_sucata_kg', 10, 2)->default(0)->after('endereco');
            }
        });

        Schema::table('transacoes_financeiras', function (Blueprint $table): void {
            if (! Schema::hasColumn('transacoes_financeiras', 'data_vencimento')) {
                $table->date('data_vencimento')->nullable()->after('data_transacao');
            }

            if (! Schema::hasColumn('transacoes_financeiras', 'status')) {
                $table->string('status')->default('pendente')->after('data_vencimento');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transacoes_financeiras', function (Blueprint $table): void {
            if (Schema::hasColumn('transacoes_financeiras', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('transacoes_financeiras', 'data_vencimento')) {
                $table->dropColumn('data_vencimento');
            }
        });

        Schema::table('clientes', function (Blueprint $table): void {
            if (Schema::hasColumn('clientes', 'saldo_sucata_kg')) {
                $table->dropColumn('saldo_sucata_kg');
            }

            if (Schema::hasColumn('clientes', 'endereco')) {
                $table->dropColumn('endereco');
            }
        });
    }
};
