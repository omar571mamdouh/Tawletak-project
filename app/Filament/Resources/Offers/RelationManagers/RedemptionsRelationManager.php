<?php

namespace App\Filament\Resources\Offers\RelationManagers;

use App\Filament\Resources\OfferRedemptions\OfferRedemptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class RedemptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'redemptions';

    protected static ?string $relatedResource = OfferRedemptionResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
