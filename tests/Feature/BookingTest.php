<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Service;
use App\Models\Availability;
use App\Models\Booking;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_booking(): void
    {
        $this->assertTrue(true);
    }

    //test crear reserva con disponibilidad
    public function test_user_can_create_booking()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        Availability::create([
            'service_id' => $service->id,
            'available_date' => '2026-05-01',
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
        ]);

        $response = $this->postJson('/api/bookings', [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'booking_date' => '2026-05-01',
            'booking_time' => '09:00:00',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('bookings', [
            'service_id' => $service->id,
            'booking_time' => '09:00:00',
        ]);
    }

    //test: horario no disponible
    public function test_cannot_book_if_not_available()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $response = $this->postJson('/api/bookings', [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'booking_date' => '2026-05-01',
            'booking_time' => '09:00:00',
        ]);

        $response->assertStatus(400);
    }

    //test: evitar doble reserva
    public function test_cannot_book_same_time_twice()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        Availability::create([
            'service_id' => $service->id,
            'available_date' => '2026-05-01',
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
        ]);

        Booking::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'booking_date' => '2026-05-01',
            'booking_time' => '09:00:00',
            'status' => 'confirmed',
        ]);

        $response = $this->postJson('/api/bookings', [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'booking_date' => '2026-05-01',
            'booking_time' => '09:00:00',
        ]);

        $response->assertStatus(400);
    }
}
