<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'phone'       => $this->phone,
            'category'    => $this->category,
            'price_range' => $this->price_range,
            'is_active'   => (bool) $this->is_active,

            'created_at'  => optional($this->created_at)->toISOString(),
            'updated_at'  => optional($this->updated_at)->toISOString(),

            // Relations (ترجع بس لو متحمّلة)
            'branches' => $this->whenLoaded('branches'),
            'staff' => RestaurantStaffResource::collection(
                $this->whenLoaded('staff')
            ),
        ];
    }
}
