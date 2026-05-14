<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BrandIdentityProfile;
use App\Models\TenantThemeVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantThemeVersionFactory extends Factory
{
    protected $model = TenantThemeVersion::class;

    public function definition(): array
    {
        return [
            'brand_identity_profile_id' => BrandIdentityProfile::factory(),
            'version_label' => 'v'.fake()->numberBetween(1, 9),
            'theme_tokens' => [
                'primary' => '#123B66',
                'secondary' => '#F59E0B',
                'surface' => '#F8FAFC',
                'accent' => '#0F766E',
                'text' => '#0F172A',
            ],
            'navigation_preferences' => [
                'template_name' => 'default',
                'show_platform_brand' => true,
            ],
            'validation_summary' => [],
            'status' => 'draft',
        ];
    }
}
