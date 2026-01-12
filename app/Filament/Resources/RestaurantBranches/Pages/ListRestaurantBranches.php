<?php

namespace App\Filament\Resources\RestaurantBranches\Pages;

use App\Filament\Resources\RestaurantBranches\RestaurantBranchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRestaurantBranches extends ListRecords
{
    protected static string $resource = RestaurantBranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
