<?php

namespace App\Filament\Resources\Visits\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VisitInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer.name')
                    ->label('Customer'),
                TextEntry::make('branch.name')
                    ->label('Branch'),
                TextEntry::make('reservation.id')
                    ->label('Reservation')
                    ->placeholder('-'),
                TextEntry::make('table.id')
                    ->label('Table')
                    ->placeholder('-'),
                TextEntry::make('seated_at')
                    ->dateTime(),
                TextEntry::make('left_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('bill_amount')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
