<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Filament\Resources\CustomerLoyalties\CustomerLoyaltyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class LoyaltiesRelationManager extends RelationManager
{
    protected static string $relationship = 'loyalties';

    protected static ?string $relatedResource = CustomerLoyaltyResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                
            ]);
    }
}
