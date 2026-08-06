<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // INDEX
    // =========================================================

    public function test_index_returns_paginated_users(): void
    {
        User::factory()->count(15)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email']
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(10, 'data'); // paginate(10)
    }

    public function test_index_returns_empty_when_no_users(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_index_returns_users_ordered_by_latest(): void
    {
        $first = User::factory()->create(['created_at' => now()->subDays(2)]);
        $last  = User::factory()->create(['created_at' => now()]);

        $response = $this->getJson('/api/users');

        // El más reciente debe aparecer primero en data[0]
        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $last->id);
    }

    // =========================================================
    // STORE
    // =========================================================

    public function test_store_creates_user_and_returns_201(): void
    {
        $payload = [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'name', 'email']])
            ->assertJsonPath('data.name', 'John Doe')
            ->assertJsonPath('data.email', 'john@example.com');


        $this->assertDatabaseHas('users', [
            'name'  => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_store_fails_with_missing_required_fields(): void
    {
        $response = $this->postJson('/api/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_store_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $payload = [
            'name'                  => 'Another User',
            'email'                 => 'taken@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_fails_with_invalid_email_format(): void
    {
        $payload = [
            'name'     => 'John Doe',
            'email'    => 'not-an-email',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // =========================================================
    // SHOW
    // =========================================================

    public function test_show_returns_user(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name', 'email']])
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_show_returns_404_for_nonexistent_user(): void
    {
        $response = $this->getJson('/api/users/999999');

        $response->assertStatus(404);
    }

    // =========================================================
    // UPDATE
    // =========================================================

    public function test_update_modifies_user_and_returns_200(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name'                  => 'Updated Name',
            'email'                 => 'updated@example.com',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.email', 'updated@example.com');

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_update_returns_404_for_nonexistent_user(): void
    {
        $payload = [
            'name'                  => 'Ghost',
            'email'                 => 'ghost@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->putJson('/api/users/999999', $payload);

        $response->assertStatus(404);
    }

    public function test_update_fails_with_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->putJson("/api/users/{$user->id}", ['email' => 'bad-email']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // =========================================================
    // DESTROY
    // =========================================================

    public function test_destroy_deletes_user_and_returns_200(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'User deleted successfully.');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_user(): void
    {
        $response = $this->deleteJson('/api/users/999999');

        $response->assertStatus(404);
    }
}
