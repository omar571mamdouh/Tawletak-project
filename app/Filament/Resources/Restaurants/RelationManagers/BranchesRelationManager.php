<?php

namespace App\Filament\Resources\Restaurants\RelationManagers;

use App\Filament\Resources\RestaurantBranches\RestaurantBranchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class BranchesRelationManager extends RelationManager
{
    protected static string $relationship = 'branches';

    protected static ?string $relatedResource = RestaurantBranchResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
