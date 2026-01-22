<?php

namespace App\Filament\Resources\RestaurantRoles\Pages;

use App\Filament\Resources\RestaurantRoles\RestaurantRoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRestaurantRoles extends ListRecords
{
    protected static string $resource = RestaurantRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
