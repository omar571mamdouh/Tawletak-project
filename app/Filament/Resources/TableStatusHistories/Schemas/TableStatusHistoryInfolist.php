<?php

namespace App\Filament\Resources\TableStatusHistories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TableStatusHistoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('table.id')
                    ->label('Table'),
                TextEntry::make('changedByStaff.name')
                    ->label('Changed by staff')
                    ->placeholder('-'),
                TextEntry::make('old_status')
                    ->badge(),
                TextEntry::make('new_status')
                    ->badge(),
                TextEntry::make('timestamp')
                    ->dateTime(),
                TextEntry::make('note')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
