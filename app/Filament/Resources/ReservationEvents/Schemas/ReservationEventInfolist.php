<?php

namespace App\Filament\Resources\ReservationEvents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ReservationEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('reservation.id')
                    ->label('Reservation'),
                TextEntry::make('event_type'),
                TextEntry::make('event_time')
                    ->dateTime(),
                TextEntry::make('actor_type')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('actor_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('meta_json')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
