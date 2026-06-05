<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\AvailabilityResource;
use App\Http\Requests\StoreAvailabilityRequest;

class AvailabilityController extends Controller
{
    function index(Request $request)
    {
        $availabilities = Availability::query()
            ->latest()
            ->paginate(10);


        return AvailabilityResource::collection($availabilities);
    }

    function store(StoreAvailabilityRequest $request): JsonResponse
    {
        $availability = Availability::create($request->validated());
        return (new AvailabilityResource($availability))
            ->response()
            ->setStatusCode(201);
    }

    function show(Availability $availability): AvailabilityResource
    {
        return new AvailabilityResource($availability);
    }

    function update(StoreAvailabilityRequest $request, Availability $availability): AvailabilityResource
    {
        $availability->update($request->validated());
        return new AvailabilityResource($availability->fresh());
    }

    function destroy(Availability $availability): JsonResponse
    {
        $availability->delete();
        return response()->json([
            'message' => 'Availability deleted successfully.',
        ], 200);
    }
}
