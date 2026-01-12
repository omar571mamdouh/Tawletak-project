<?php

namespace App\Filament\Resources\Restaurants\RelationManagers;

use App\Filament\Resources\RestaurantStaff\RestaurantStaffResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class StaffRelationManager extends RelationManager
{
    protected static string $relationship = 'staff';

    protected static ?string $relatedResource = RestaurantStaffResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
