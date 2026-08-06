<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Availability;
use Illuminate\Support\Facades\DB;


class BookingController extends Controller
{

    public function index()
    {
        $bookings = Booking::query()
            ->with(['user', 'employee', 'service'])
            ->latest()
            ->paginate(5);
        return BookingResource::collection($bookings);
    }

    public function store(StoreBookingRequest $request)
    {
        $validateData = $request->validated();

        try {
            $booking = DB::transaction(function () use ($validateData) {

                // verificar disponibilidad
                $isAvailable = Availability::where('employee_id', $validateData['employee_id'])
                    ->where('available_date', $validateData['booking_date'])
                    ->where('start_time', '<=', $validateData['booking_time'])
                    ->where('end_time', '>=', $validateData['booking_time'])
                    ->exists();

                if (!$isAvailable) {
                    throw new \Exception('Horario no disponible', 400);
                }

                // evitar reservas duplicadas
                $exists = Booking::where('employee_id', $validateData['employee_id'])
                    ->where('booking_date', $validateData['booking_date'])
                    ->where('booking_time', $validateData['booking_time'])
                    ->exists();

                if ($exists) {
                    throw new \Exception('Ya existe una reserva para este espacio de tiempo', 400);
                }

                //crear reserva
                $booking = Booking::create([
                    'user_id'      => $validateData['user_id'],
                    'employee_id'  => $validateData['employee_id'],
                    'service_id'   => $validateData['service_id'],
                    'booking_date' => $validateData['booking_date'],
                    'booking_time' => $validateData['booking_time'],
                    'price_at_booking' => $validateData['price_at_booking'] ?? null,

                ]);

                // actualizar disponibilidad
                $availability = Availability::where('employee_id', $validateData['employee_id'])
                    ->where('available_date', $validateData['booking_date'])
                    ->where('start_time', $validateData['booking_time'])
                    ->first();

                return (new BookingResource($booking))->resolve();
            });

            return response()->json($booking, 201);
        } catch (\Exception $e) {
            // Distinguir errores de negocio (400) de errores inesperados (500)
            $code = $e->getCode() === 400 ? 400 : 500;

            return response()->json([
                'message' => $e->getMessage()
            ], $code);
        }
    }

    public function show(Booking $booking): BookingResource
    {
        return new BookingResource($booking);
    }

    public function update(StoreBookingRequest $request, Booking $booking): BookingResource
    {
        //validar datos
        $validateData = $request->validated();

        //actualizar reserva
        $booking->update([
            'booking_date' => $validateData['booking_date'],
            'booking_time' => $validateData['booking_time'],
            'status'       => $validateData['status'],
            'price_at_booking' => $validateData['price_at_booking'] ?? $booking->price_at_booking,
        ]);

        return new BookingResource($booking);
    }

    public function destroy(Booking $booking)
    {
        //eliminar reserva
        $booking->delete();
        return new BookingResource($booking);
    }
}
