<?php

namespace App\Filament\Resources\RestaurantStaff\Pages;

use App\Filament\Resources\RestaurantStaff\RestaurantStaffResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRestaurantStaff extends CreateRecord
{
    protected static string $resource = RestaurantStaffResource::class;


    protected function afterCreate(): void
{
    $roleId = $this->data['restaurant_role_id'] ?? null;

    if ($roleId) {
        $this->record->roleAssignment()->updateOrCreate(
            ['staff_id' => $this->record->id],
            ['restaurant_role_id' => $roleId]
        );
    }
}

}
