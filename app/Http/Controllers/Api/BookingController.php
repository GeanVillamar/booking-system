<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    /**
     * Store a newly created booking in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bookings = Booking::with(['user', 'service'])->get();
        return response()->json($bookings, 200);
    }

    /**
     * Store a newly created booking in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */




    public function store(Request $request)
    {
        // $validatedData = $request->validate([
        //     'user_id' => 'required|exists:users,id',
        //     'service_id' => 'required|exists:services,id',
        //     'booking_date' => 'required|date',
        //     'booking_time' => 'required|date_format:H:i',
        // ]);

        //$booking = Booking::create($validatedData);
        $booking = Booking::create($request->all());
        return response()->json($booking, 201);
    }

    public function show($id)
    {
        $booking = Booking::with(['user', 'service'])->findOrFail($id);
        return response()->json($booking, 200);
    }
}
