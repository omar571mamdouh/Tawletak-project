<?php

namespace App\Filament\Resources\RestaurantBranches\RelationManagers;

use App\Filament\Resources\Offers\OfferResource; // عدّل حسب مكان OfferResource عندك
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use App\Filament\Resources\RestaurantBranches\RelationManagers\OffersRelationManager;

class OffersRelationManager extends RelationManager
{
    protected static string $relationship = 'offers';

    protected static ?string $relatedResource = OfferResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
