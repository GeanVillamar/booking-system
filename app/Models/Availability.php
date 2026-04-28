<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    protected $fillable = [
        'service_id',
        'available_date',
        'start_time',
        'end_time',
    ];
}
