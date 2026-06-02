<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\EmployeeResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'booking_date' => $this->booking_date,
            'booking_time' => $this->booking_time,
            'price_at_booking' => $this->price_at_booking,
        ];
    }
}
