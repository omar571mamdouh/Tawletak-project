<?php

namespace App\Filament\Resources\RestaurantRoles\Pages;

use App\Filament\Resources\RestaurantRoles\RestaurantRoleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRestaurantRole extends EditRecord
{
    protected static string $resource = RestaurantRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
