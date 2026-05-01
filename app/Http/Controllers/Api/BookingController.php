<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Http\Controllers\Controller;
use App\Models\Availability;

class BookingController extends Controller
{

    public function index()
    {
        $bookings = Booking::with(['user', 'service'])->get();
        return response()->json($bookings, 200);
    }

    public function store(Request $request)
    {

        // 1. Validar disponibilidad (Availabilities)
        $isAvailable = Availability::where('service_id', $request->service_id)
            ->where('available_date', $request->booking_date)
            ->where('start_time', '<=', $request->booking_time)
            ->where('end_time', '>', $request->booking_time)
            ->exists();

        if (!$isAvailable) {
            return response()->json([
                'message' => 'Horario no disponible'
            ], 400);
        }

        // 2. Validar que no esté ocupado (Bookings)
        $exists = Booking::where('service_id', $request->service_id)
            ->where('booking_date', $request->booking_date)
            ->where('booking_time', $request->booking_time)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Este horario ya está reservado'
            ], 400);
        }

        // 3. Crear reserva
        Booking::create([
            'user_id' => $request->user_id,
            'service_id' => $request->service_id,
            'booking_date' => $request->booking_date,
            'booking_time' => $request->booking_time,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Reserva creada correctamente'
        ]);
    }

    public function show($id)
    {
        $booking = Booking::with(['user', 'service'])->findOrFail($id);
        return response()->json($booking, 200);
    }
}
