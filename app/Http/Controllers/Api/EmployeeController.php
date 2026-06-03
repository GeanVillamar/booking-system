<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    function index()
    {
        $employees = \App\Models\Employee::all();
        return response()->json($employees, 200);
    }

    function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'specialty' => 'required|string',
            'email' => 'required|email|unique:employees,email',
        ]);

        $employee = \App\Models\Employee::create($validated);
        $employee->services()->attach($request->service_id);

        return response()->json($employee, 201);
    }

    function show(int $id)
    {
        $employee = \App\Models\Employee::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json($employee, 200);
    }
}
