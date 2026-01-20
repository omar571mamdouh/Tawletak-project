<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Filament\Resources\Waitlists\WaitlistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class WaitlistsRelationManager extends RelationManager
{
    protected static string $relationship = 'waitlists';

    protected static ?string $relatedResource = WaitlistResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
               
            ]);
    }
}
