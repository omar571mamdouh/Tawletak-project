<?php

namespace App\Filament\Resources\Waitlists\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WaitlistInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer.name')
                    ->label('Customer'),
                TextEntry::make('branch.name')
                    ->label('Branch'),
                TextEntry::make('party_size')
                    ->numeric(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('estimated_wait_minutes')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('notified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('seated_at')
                    ->dateTime()
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
