<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Filament\Resources\OfferRedemptions\OfferRedemptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class OfferRedemptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'OfferRedemptions';

    protected static ?string $relatedResource = OfferRedemptionResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
