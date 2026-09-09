<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_returns_a_successful_response()
    {
        $response = $this->getJson('/');

        $response->assertOk()->assertJson(['status' => 'ok']);
    }

    public function test_api_health_endpoint_responds()
    {
        $this->getJson('/api/health')->assertOk()->assertJson(['status' => 'ok']);
    }
}
