<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProviderPortalTest extends TestCase
{
    public function test_guest_cannot_access_provider_dashboard(): void
    {
        $response = $this->getJson('/api/v1/provider/dashboard');

        $response->assertStatus(401);
    }
}
