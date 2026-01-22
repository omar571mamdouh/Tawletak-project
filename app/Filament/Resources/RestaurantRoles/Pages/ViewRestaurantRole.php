<?php

namespace App\Filament\Resources\RestaurantRoles\Pages;

use App\Filament\Resources\RestaurantRoles\RestaurantRoleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRestaurantRole extends ViewRecord
{
    protected static string $resource = RestaurantRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
