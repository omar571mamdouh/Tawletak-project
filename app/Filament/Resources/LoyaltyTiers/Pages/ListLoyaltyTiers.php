<?php

namespace App\Filament\Resources\LoyaltyTiers\Pages;

use App\Filament\Resources\LoyaltyTiers\LoyaltyTierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyTiers extends ListRecords
{
    protected static string $resource = LoyaltyTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
