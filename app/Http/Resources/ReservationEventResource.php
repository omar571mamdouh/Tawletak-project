<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'reservation_id' => $this->reservation_id,
            'type'           => $this->type,        // أو event_type حسب جدولك
            'note'           => $this->note,        // لو عندك
            'created_at'     => optional($this->created_at)->toISOString(),
        ];
    }
}
