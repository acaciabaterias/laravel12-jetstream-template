<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TenantThemeVersion;
use App\Models\ThemeRollbackEvidence;
use App\Models\UsuarioPlataforma;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeRollbackEvidenceFactory extends Factory
{
    protected $model = ThemeRollbackEvidence::class;

    public function definition(): array
    {
        return [
            'tenant_theme_version_id' => TenantThemeVersion::factory(),
            'restored_theme_version_id' => null,
            'operator_id' => UsuarioPlataforma::factory(),
            'reason' => fake()->sentence(),
            'evidence_payload' => ['source' => 'factory'],
            'rolled_back_at' => now(),
        ];
    }
}
