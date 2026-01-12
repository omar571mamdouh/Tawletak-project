<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ReservationEventResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'branch_id' => $this->branch_id,
            'table_id' => $this->table_id,
            'party_size' => (int) $this->party_size,
            'reservation_time' => optional($this->reservation_time)->toISOString(),
            'expected_duration_minutes' => $this->expected_duration_minutes ? (int) $this->expected_duration_minutes : null,
            'status' => $this->status,
            'source' => $this->source,

            'confirmed_at' => optional($this->confirmed_at)->toISOString(),
            'cancelled_at' => optional($this->cancelled_at)->toISOString(),
            'seated_at' => optional($this->seated_at)->toISOString(),
            'completed_at' => optional($this->completed_at)->toISOString(),

            'customer' => $this->whenLoaded('customer'),
            'branch' => $this->whenLoaded('branch'),
            'table' => $this->whenLoaded('table'),
            'events' => ReservationEventResource::collection($this->whenLoaded('events')),

        ];
    }
}
