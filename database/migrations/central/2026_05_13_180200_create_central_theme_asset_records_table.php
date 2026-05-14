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
        if (Schema::connection($this->connection)->hasTable('theme_asset_records')) {
            return;
        }

        Schema::connection($this->connection)->create('theme_asset_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('brand_identity_profile_id')->constrained('brand_identity_profiles')->cascadeOnDelete();
            $table->string('asset_type', 40);
            $table->text('storage_reference');
            $table->string('mime_type', 100)->nullable();
            $table->string('checksum', 100)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestampsTz();

            $table->index(['brand_identity_profile_id', 'asset_type']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('theme_asset_records');
    }
};
