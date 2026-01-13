<?php

namespace App\Filament\Resources\OfferRedemptions\Pages;

use App\Filament\Resources\OfferRedemptions\OfferRedemptionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOfferRedemption extends EditRecord
{
    protected static string $resource = OfferRedemptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
