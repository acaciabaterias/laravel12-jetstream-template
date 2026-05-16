<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection($this->connection)->table('notas_fiscais_orquestradas', function (Blueprint $table) {
            $table->unsignedBigInteger('certificado_digital_id')->nullable()->after('idempotency_key');
            $table->string('certificado_referencia', 150)->nullable()->after('certificado_digital_id');
        });

        Schema::connection($this->connection)->table('boletos_orquestrados', function (Blueprint $table) {
            $table->unsignedBigInteger('certificado_digital_id')->nullable()->after('idempotency_key');
            $table->string('certificado_referencia', 150)->nullable()->after('certificado_digital_id');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('notas_fiscais_orquestradas', function (Blueprint $table) {
            $table->dropColumn(['certificado_digital_id', 'certificado_referencia']);
        });

        Schema::connection($this->connection)->table('boletos_orquestrados', function (Blueprint $table) {
            $table->dropColumn(['certificado_digital_id', 'certificado_referencia']);
        });
    }
};
