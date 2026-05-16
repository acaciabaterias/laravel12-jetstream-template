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
        if (Schema::connection($this->connection)->hasTable('executive_report_definitions')) {
            return;
        }

        Schema::connection($this->connection)->create('executive_report_definitions', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $this->jsonColumn($table, 'default_filters');
            $this->jsonColumn($table, 'visible_sections');
            $this->jsonColumn($table, 'supported_formats');
            $table->string('status', 20)->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('executive_report_definitions');
    }

    private function usesPostgres(): bool
    {
        return DB::connection($this->connection)->getDriverName() === 'pgsql';
    }

    private function jsonColumn(Blueprint $table, string $column): void
    {
        if ($this->usesPostgres()) {
            $table->jsonb($column)->default(DB::raw("'[]'::jsonb"));

            return;
        }

        $table->json($column)->nullable();
    }
};
