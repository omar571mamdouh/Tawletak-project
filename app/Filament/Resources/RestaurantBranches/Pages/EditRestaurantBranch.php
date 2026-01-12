<?php

namespace App\Filament\Resources\RestaurantBranches\Pages;

use App\Filament\Resources\RestaurantBranches\RestaurantBranchResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRestaurantBranch extends EditRecord
{
    protected static string $resource = RestaurantBranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
