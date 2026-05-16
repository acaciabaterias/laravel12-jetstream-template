<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TenantThemeVersion;
use App\Models\ThemePublicationRecord;
use App\Models\UsuarioPlataforma;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemePublicationRecordFactory extends Factory
{
    protected $model = ThemePublicationRecord::class;

    public function definition(): array
    {
        return [
            'tenant_theme_version_id' => TenantThemeVersion::factory(),
            'environment' => 'staging',
            'operator_id' => UsuarioPlataforma::factory(),
            'validation_passed' => true,
            'validation_messages' => [],
            'published_snapshot' => [],
            'status' => 'published',
            'published_at' => now(),
        ];
    }
}
