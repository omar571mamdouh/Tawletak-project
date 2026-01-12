<?php

namespace App\Filament\Resources\RestaurantStaff\Pages;

use App\Filament\Resources\RestaurantStaff\RestaurantStaffResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRestaurantStaff extends ViewRecord
{
    protected static string $resource = RestaurantStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
