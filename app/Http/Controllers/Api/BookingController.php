<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Availability;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class BookingController extends Controller
{

    public function index()
    {
        $bookings = Booking::with(['user', 'service'])->get();
        return response()->json($bookings, 200);
    }

    public function store(StoreBookingRequest $request)
    {

        //validar datos desde el FormRequest
        $validateData = $request->validated();

        try {
            $booking = DB::transaction(function () use ($validateData) {

                // evitar reservas duplicadas
                $exists = Booking::where('service_id', $validateData['service_id'])
                    ->where('booking_date', $validateData['booking_date'])
                    ->where('booking_time', $validateData['booking_time'])
                    ->exists();

                if ($exists) {
                    abort(400, 'Booking already exists for this time slot');
                }

                $booking = Booking::create([
                    'user_id'      => $validateData['user_id'],
                    'service_id'   => $validateData['service_id'],
                    'booking_date' => $validateData['booking_date'],
                    'booking_time' => $validateData['booking_time'],
                ]);

                // actualizar disponibilidad
                $availability = Availability::where('service_id', $validateData['service_id'])
                    ->where('available_date', $validateData['booking_date'])
                    ->where('start_time', $validateData['booking_time'])
                    ->first();

                if ($availability) {
                    $availability->update([
                        'is_available' => false
                    ]);
                }

                return $booking;
            });

            return response()->json($booking, 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id)
    {
        $booking = Booking::with(['user', 'service'])->findOrFail($id);
        return response()->json($booking, 200);
    }

    public function update(StoreBookingRequest $request, int $id)
    {
        $booking = Booking::findOrFail($id);

        //validar datos
        $validateData = $request->validated();

        //actualizar reserva
        $booking->update([
            'booking_date' => $validateData['booking_date'],
            'booking_time' => $validateData['booking_time'],
            'status'       => $validateData['status'],
        ]);

        return response()->json($booking, 200);
    }

    public function destroy(int $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();
        return response()->json(null, 204);
    }
}
