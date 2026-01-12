<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantStaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'branch_id'     => $this->branch_id,
            'name'          => $this->name,
            'phone'         => $this->phone,
            'email'         => $this->email,
            'role'          => $this->role,
            'is_active'     => (bool) $this->is_active,
            'created_at'    => optional($this->created_at)->toISOString(),
            'updated_at'    => optional($this->updated_at)->toISOString(),
        ];
    }
}
