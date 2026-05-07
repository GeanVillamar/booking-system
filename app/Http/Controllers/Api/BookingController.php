<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Http\Controllers\Controller;
use App\Models\Availability;
use Illuminate\Support\Facades\Validator;

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
        $booking = Booking::create([
            'user_id' => $request->user_id,
            'service_id' => $request->service_id,
            'booking_date' => $request->booking_date,
            'booking_time' => $request->booking_time,
        ]);

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
}
