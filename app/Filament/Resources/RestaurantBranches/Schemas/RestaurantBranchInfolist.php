<?php

namespace App\Filament\Resources\RestaurantBranches\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RestaurantBranchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('restaurant.name')
                    ->label('Restaurant'),
                TextEntry::make('name'),
                TextEntry::make('address'),
                TextEntry::make('lat')
                    ->numeric(),
                TextEntry::make('lng')
                    ->numeric(),
                TextEntry::make('opening_time')
                    ->time(),
                TextEntry::make('closing_time')
                    ->time(),
                TextEntry::make('timezone'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
