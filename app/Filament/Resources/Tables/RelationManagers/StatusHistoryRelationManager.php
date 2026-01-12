<?php

namespace App\Filament\Resources\Tables\RelationManagers;

use App\Filament\Resources\TableStatusHistories\TableStatusHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class StatusHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistory';

    protected static ?string $relatedResource = TableStatusHistoryResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
               
            ]);
    }
}
