<?php

namespace App\Filament\Resources\CustomerLoyalties\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerLoyaltyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer.name')
                    ->label('Customer'),
                TextEntry::make('restaurant.name')
                    ->label('Restaurant'),
                TextEntry::make('visit_count')
                    ->numeric(),
                TextEntry::make('tier.name')
                    ->label('Tier'),
                TextEntry::make('last_visit_at')
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
