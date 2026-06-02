<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'user_id',
        'employee_id',
        'service_id',
        'booking_date',
        'booking_time',
        'status',
        'price_at_booking',
    ];

    /** 
     * Get the user that owns the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /** 
     * Get the employee that is assigned to the booking.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    /** 
     * Get the service that is booked.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
