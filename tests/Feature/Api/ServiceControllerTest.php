<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Service;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * INDEX.
     */
    public function test_index_returns_paginated_services(): void
    {

        Service::factory()->count(15)->create();
        $response = $this->getJson('api/services');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'price']
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(10, 'data'); // paginate(10)
    }
    /**
     * STORE.
     */
    public function test_store_creates_service_and_returns_201(): void
    {
        $payload = [
            'name' => 'Test Service',
            'description' => 'This is a test service.',
            'price' => 99.99,
        ];

        $response = $this->postJson('api/services', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Test Service',
                'description' => 'This is a test service.',
                'price' => 99.99,
            ]);

        $this->assertDatabaseHas('services', [
            'name' => 'Test Service',
            'description' => 'This is a test service.',
            'price' => 99.99,
        ]);
    }

    // =========================================================
    // UPDATE
    // =========================================================
    public function test_update_modifies_service_and_returns_200(): void
    {

        Sanctum::actingAs(User::factory()->create());
        $service = Service::factory()->create();

        $payload = [
            'name' => 'Updated Service',
            'description' => 'This service has been updated.',
            'price' => 149.99,
        ];

        $response = $this->putJson("api/services/{$service->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $service->id,
                'name' => 'Updated Service',
                'description' => 'This service has been updated.',
                'price' => 149.99,
            ]);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Updated Service',
            'description' => 'This service has been updated.',
            'price' => 149.99,
        ]);
    }
}
