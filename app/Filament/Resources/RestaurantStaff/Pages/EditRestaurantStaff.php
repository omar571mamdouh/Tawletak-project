<?php

namespace App\Filament\Resources\RestaurantStaff\Pages;

use App\Filament\Resources\RestaurantStaff\RestaurantStaffResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRestaurantStaff extends EditRecord
{
    protected static string $resource = RestaurantStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
