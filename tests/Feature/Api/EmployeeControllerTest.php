<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Employee;
use Tests\TestCase;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * INDEX.   
     */
    public function test_index_returns_paginated_employees(): void
    {
        Employee::factory()->count(5)->create();
        $response = $this->getJson('api/employees');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'specialty', 'email']
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(5, 'data'); // paginate(10)
    }
    /**
     * STORE.
     */
    public function test_store_creates_employee_and_returns_201(): void
    {
        $payload = [
            'name' => 'John Doe',
            'specialty' => 'Odontologist',
            'email' => 'john.doe@example.com'
        ];

        $response = $this->postJson('api/employees', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'John Doe',
                'specialty' => 'Odontologist',
                'email' => 'john.doe@example.com'
            ]);

        $this->assertDatabaseHas('employees', [
            'name' => 'John Doe',
            'specialty' => 'Odontologist',
            'email' => 'john.doe@example.com'
        ]);
    }
    /**
     * UPDATE.
     */
    public function test_update_modifies_employee_and_returns_200(): void
    {
        $employee = Employee::factory()->create();

        $payload = [
            'name' => 'Updated Name',
            'specialty' => 'Updated Specialty',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson("api/employees/{$employee->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $employee->id,
                'name' => 'Updated Name',
                'specialty' => 'Updated Specialty',
                'email' => 'updated@example.com',
            ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'Updated Name',
            'specialty' => 'Updated Specialty',
            'email' => 'updated@example.com',
        ]);
    }

    /***
     * DESTROY.
     */
    public function test_destroy_deletes_employee_and_returns_204(): void
    {
        $employee = Employee::factory()->create();
        $response = $this->deleteJson("api/employees/{$employee->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('employees', [
            'id' => $employee->id,
        ]);
    }
}
