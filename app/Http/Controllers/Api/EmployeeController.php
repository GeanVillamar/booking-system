<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Http\Requests\StoreEmployeeRequest;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::query()
            ->latest()
            ->paginate(5);
        return EmployeeResource::collection($employees);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $Employee = Employee::create($request->validated());
        $Employee->services()->attach($request->service_id);
        return (new EmployeeResource($Employee))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Employee $employee): EmployeeResource
    {
        return new EmployeeResource($employee);
    }

    public function update(StoreEmployeeRequest $request, Employee $employee): EmployeeResource
    {
        $employee->update($request->validated());
        $employee->services()->sync($request->service_id);

        return new EmployeeResource($employee->fresh());
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return response()->json([
            'message' => 'Employee deleted successfully.',
        ], 204);
    }
}
