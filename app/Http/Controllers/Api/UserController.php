<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    function index()
    {
        $users = User::all();
        return response()->json($users, 200);
    }

    function store(Request $request)
    {
        $user = User::create($request->all());
        return response()->json($user, 201);
    }

    function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user, 200);
    }
}
