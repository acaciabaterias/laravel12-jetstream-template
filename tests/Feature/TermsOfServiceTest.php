<?php

namespace Tests\Feature;

use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class TermsOfServiceTest extends TestCase
{
    public function test_terms_of_service_screen_can_be_rendered(): void
    {
        if (! Jetstream::hasTermsAndPrivacyPolicyFeature()) {
            $this->markTestSkipped('Terms of service support is not enabled.');
        }

        $response = $this->get('/terms-of-service');

        $response->assertStatus(200);
    }

    public function test_privacy_policy_screen_can_be_rendered(): void
    {
        if (! Jetstream::hasTermsAndPrivacyPolicyFeature()) {
            $this->markTestSkipped('Terms of service support is not enabled.');
        }

        $response = $this->get('/privacy-policy');

        $response->assertStatus(200);
    }
}
