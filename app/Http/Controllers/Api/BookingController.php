<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Http\Controllers\Controller;
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

    public function store(Request $request)
    {

        //validar datos
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|exists:users,id',
            'service_id'   => 'required|exists:services,id',
            'booking_date' => 'required|date',
            'booking_time' => 'required|date_format:H:i',
            'status'       => 'nullable|in:pending,confirmed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        //evitar reservas duplicadas
        $existingBooking = Booking::where('service_id', $request->service_id)
            ->where('booking_date', $request->booking_date)
            ->where('booking_time', $request->booking_time)
            ->first();
        if ($existingBooking) {
            return response()->json(['error' => 'Booking already exists for this time slot'], 400);
        }

        //crear reserva
        // agregar transacción para asegurar que la reserva y la actualización de disponibilidad se realicen juntas
        // $booking = Booking::create([
        //     'user_id' => $request->user_id,
        //     'service_id' => $request->service_id,
        //     'booking_date' => $request->booking_date,
        //     'booking_time' => $request->booking_time,
        // ]);

        $booking = DB::transaction(function () use ($request) {
            $booking = Booking::create([
                'user_id'      => $request->user_id, // ← del token, no del request
                'service_id'   => $request->service_id,
                'booking_date' => $request->booking_date, // ← reutilizar dato
                'booking_time' => $request->booking_time,     // ← reutilizar dato
            ]);

            return $booking;
        });


        //marcar disponibilidad como no disponible
        $availability = Availability::where('service_id', $request->service_id)
            ->where('available_date', $request->available_date)
            ->where('start_time', $request->start_time)
            ->where('end_time', $request->end_time)
            ->first();
        if ($availability) {
            $availability->update(['is_available' => false]);
        }

        return response()->json($booking, 201);
    }

    public function show(int $id)
    {
        $booking = Booking::with(['user', 'service'])->findOrFail($id);
        return response()->json($booking, 200);
    }

    public function update(Request $request, int $id)
    {
        $booking = Booking::findOrFail($id);

        //validar datos
        $validator = Validator::make($request->all(), [
            'booking_date' => 'sometimes|required|date',
            'booking_time' => 'sometimes|required|date_format:H:i',
            'status'       => 'sometimes|in:pending,confirmed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        //actualizar reserva
        $booking->update($request->only(['booking_date', 'booking_time', 'status']));

        return response()->json($booking, 200);
    }
}
