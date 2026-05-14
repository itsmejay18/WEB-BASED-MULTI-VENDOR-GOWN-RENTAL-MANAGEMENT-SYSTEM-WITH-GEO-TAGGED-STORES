<?php

namespace Tests\Feature;

use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    public function test_guest_cannot_create_booking(): void
    {
        $response = $this->postJson('/api/v1/bookings', []);

        $response->assertStatus(401);
    }
}
