<?php

namespace App\Filament\Resources\RestaurantStaff\Pages;

use App\Filament\Resources\RestaurantStaff\RestaurantStaffResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRestaurantStaff extends ListRecords
{
    protected static string $resource = RestaurantStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
