<?php

namespace App\Filament\Resources\RestaurantBranches\Pages;

use App\Filament\Resources\RestaurantBranches\RestaurantBranchResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRestaurantBranch extends ViewRecord
{
    protected static string $resource = RestaurantBranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
