<?php

namespace App\Filament\Resources\LoyaltyTiers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LoyaltyTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('name')
                    ->options(['Bronze' => 'Bronze', 'Silver' => 'Silver', 'Gold' => 'Gold'])
                    ->required(),
                TextInput::make('min_visits')
                    ->required()
                    ->numeric(),
                Textarea::make('benefits_json')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
