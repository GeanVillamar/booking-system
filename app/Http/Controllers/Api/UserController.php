<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    function index()
    {
        $users = User::query()
            ->latest()
            ->paginate(10);
        return UserResource::collection($users);
    }

    function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());
        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    function update(StoreUserRequest $request, User $user): UserResource
    {
        $user->update($request->validated());
        return new UserResource($user->fresh());
    }

    function destroy(User $user): JsonResponse
    {
        $user->delete();
        return response()->json([
            'message' => 'User deleted successfully.',
        ], 200);
    }
}
