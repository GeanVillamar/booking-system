<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;


class AvailabilityController extends Controller
{
    function index(Request $request)
    {
        $availabilities = \App\Models\Availability::where('service_id', $request->service_id)
            ->where('available_date', $request->available_date)
            ->get();

        return response()->json($availabilities, 200);
    }

    function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'available_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $availability = \App\Models\Availability::create($validated);

        return response()->json($availability, 201);
    }

    function show(int $id)
    {
        $availability = \App\Models\Availability::find($id);

        if (!$availability) {
            return response()->json(['message' => 'Availability not found'], 404);
        }

        return response()->json($availability, 200);
    }
}
