<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiAuthMiddlewareTest extends TestCase
{
    public function test_protected_routes_return_401_for_unauthenticated_requests(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }
}
