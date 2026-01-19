<?php

namespace App\Filament\Resources\Restaurants\RelationManagers;

use App\Filament\Resources\MenuSections\MenuSectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class MenuSectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'menuSections';

    protected static ?string $relatedResource = MenuSectionResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
