<?php

namespace App\Filament\Resources\TableStatuses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TableStatusInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('table.id')
                    ->label('Table'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('currentReservation.id')
                    ->label('Current reservation')
                    ->placeholder('-'),
                TextEntry::make('occupied_since')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('estimated_free_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
