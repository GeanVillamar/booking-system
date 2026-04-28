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
}
