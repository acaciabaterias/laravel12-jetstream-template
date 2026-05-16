<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BrandIdentityProfile;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class BrandIdentityProfileFactory extends Factory
{
    protected $model = BrandIdentityProfile::class;

    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'brand_name' => fake()->company(),
            'brand_slug' => fake()->unique()->slug(),
            'login_title' => fake()->company().' ERP',
            'default_font_family' => 'Poppins',
            'default_theme_tokens' => [
                'primary' => '#123B66',
                'secondary' => '#F59E0B',
                'surface' => '#F8FAFC',
                'accent' => '#0F766E',
                'text' => '#0F172A',
            ],
            'status' => 'draft',
        ];
    }
}
