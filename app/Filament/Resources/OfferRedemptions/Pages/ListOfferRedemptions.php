<?php

namespace App\Filament\Resources\OfferRedemptions\Pages;

use App\Filament\Resources\OfferRedemptions\OfferRedemptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOfferRedemptions extends ListRecords
{
    protected static string $resource = OfferRedemptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
