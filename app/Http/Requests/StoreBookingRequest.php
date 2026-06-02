<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id'      => 'required|exists:users,id',
            'employee_id'  => 'nullable|exists:employees,id',
            'service_id'   => 'required|exists:services,id',
            'booking_date' => 'required|date',
            'booking_time' => 'required|date_format:H:i',
            'status'       => 'nullable|in:pending,confirmed,cancelled',
            'price_at_booking' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID is required',
            'user_id.exists'   => 'User ID must exist in users table',
            'employee_id.nullable' => 'Employee ID must be a valid employee',
            'employee_id.exists' => 'Employee ID must exist in employees table',
            'service_id.required' => 'Service ID is required',
            'service_id.exists'   => 'Service ID must exist in services table',
            'booking_date.required' => 'Booking date is required',
            'booking_date.date'     => 'Booking date must be a valid date',
            'booking_time.required' => 'Booking time is required',
            'booking_time.date_format' => 'Booking time must be in H:i format',
            'status.in' => 'Status must be one of: pending, confirmed, cancelled',
            'price_at_booking.min' => 'Price at booking must be a positive number',
        ];
    }
}
