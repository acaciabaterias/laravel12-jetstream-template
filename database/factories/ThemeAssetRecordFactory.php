<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BrandIdentityProfile;
use App\Models\ThemeAssetRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeAssetRecordFactory extends Factory
{
    protected $model = ThemeAssetRecord::class;

    public function definition(): array
    {
        return [
            'brand_identity_profile_id' => BrandIdentityProfile::factory(),
            'asset_type' => 'logo_primary',
            'storage_reference' => fake()->imageUrl(),
            'mime_type' => 'image/png',
            'checksum' => sha1((string) fake()->uuid()),
            'status' => 'active',
        ];
    }
}
