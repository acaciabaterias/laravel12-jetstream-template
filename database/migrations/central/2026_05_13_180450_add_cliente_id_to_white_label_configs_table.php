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
        if (! Schema::connection($this->connection)->hasTable('white_label_configs')) {
            return;
        }

        if (! Schema::connection($this->connection)->hasColumn('white_label_configs', 'cliente_id')) {
            Schema::connection($this->connection)->table('white_label_configs', function (Blueprint $table): void {
                $table->unsignedBigInteger('cliente_id')->nullable()->after('id');
            });
        }

        $firstClienteId = DB::connection($this->connection)->table('clientes')->orderBy('id')->value('id');

        if ($firstClienteId !== null) {
            DB::connection($this->connection)->table('white_label_configs')
                ->whereNull('cliente_id')
                ->update(['cliente_id' => $firstClienteId]);
        }

        if (! $this->hasForeignKey('white_label_configs', 'white_label_configs_cliente_id_foreign')) {
            Schema::connection($this->connection)->table('white_label_configs', function (Blueprint $table): void {
                $table->foreign('cliente_id')->references('id')->on('clientes')->cascadeOnDelete();
            });
        }

        if (! $this->hasIndex('white_label_configs', 'white_label_configs_cliente_id_unique')) {
            Schema::connection($this->connection)->table('white_label_configs', function (Blueprint $table): void {
                $table->unique('cliente_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::connection($this->connection)->hasTable('white_label_configs')) {
            return;
        }

        Schema::connection($this->connection)->table('white_label_configs', function (Blueprint $table): void {
            if ($this->hasIndex('white_label_configs', 'white_label_configs_cliente_id_unique')) {
                $table->dropUnique('white_label_configs_cliente_id_unique');
            }

            if ($this->hasForeignKey('white_label_configs', 'white_label_configs_cliente_id_foreign')) {
                $table->dropForeign('white_label_configs_cliente_id_foreign');
            }

            if (Schema::connection($this->connection)->hasColumn('white_label_configs', 'cliente_id')) {
                $table->dropColumn('cliente_id');
            }
        });
    }

    private function hasForeignKey(string $table, string $foreignKeyName): bool
    {
        if (DB::connection($this->connection)->getDriverName() !== 'pgsql') {
            return false;
        }

        return DB::connection($this->connection)->table('information_schema.table_constraints')
            ->where('table_name', $table)
            ->where('constraint_name', $foreignKeyName)
            ->exists();
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        if (DB::connection($this->connection)->getDriverName() !== 'pgsql') {
            return false;
        }

        return DB::connection($this->connection)->table('pg_indexes')
            ->where('tablename', $table)
            ->where('indexname', $indexName)
            ->exists();
    }
};
