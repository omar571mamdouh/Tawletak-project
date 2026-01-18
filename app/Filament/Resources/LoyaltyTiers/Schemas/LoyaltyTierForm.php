<?php

namespace App\Filament\Resources\LoyaltyTiers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LoyaltyTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tier Name')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Bronze, Silver, Gold, Platinum, Diamond')
                    ->unique(ignoreRecord: true),

                TextInput::make('min_visits')
                    ->label('Minimum Visits')
                    ->required()
                    ->numeric(),

                Textarea::make('benefits_json')
                    ->label('Benefits')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
