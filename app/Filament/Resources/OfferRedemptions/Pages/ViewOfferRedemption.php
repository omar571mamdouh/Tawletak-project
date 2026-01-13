<?php

namespace App\Filament\Resources\OfferRedemptions\Pages;

use App\Filament\Resources\OfferRedemptions\OfferRedemptionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOfferRedemption extends ViewRecord
{
    protected static string $resource = OfferRedemptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
