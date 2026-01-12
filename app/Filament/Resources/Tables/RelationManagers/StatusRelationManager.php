<?php

namespace App\Filament\Resources\Tables\RelationManagers;

use App\Filament\Resources\TableStatuses\TableStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class StatusRelationManager extends RelationManager
{
    protected static string $relationship = 'status';

    protected static ?string $relatedResource = TableStatusResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
              
            ]);
    }
}
