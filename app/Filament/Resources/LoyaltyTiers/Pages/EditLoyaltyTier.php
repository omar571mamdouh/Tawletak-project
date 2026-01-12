<?php

namespace App\Filament\Resources\LoyaltyTiers\Pages;

use App\Filament\Resources\LoyaltyTiers\LoyaltyTierResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyTier extends EditRecord
{
    protected static string $resource = LoyaltyTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
