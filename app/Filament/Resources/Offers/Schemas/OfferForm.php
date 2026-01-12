<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('discount_type')
                    ->options(['percent' => 'Percent', 'fixed' => 'Fixed', 'perk' => 'Perk'])
                    ->required(),
                TextInput::make('discount_value')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('start_at')
                    ->required(),
                DateTimePicker::make('end_at')
                    ->required(),
                TextInput::make('min_party_size')
                    ->numeric()
                    ->default(null),
                Select::make('eligible_loyalty_tier')
                    ->options(['Bronze' => 'Bronze', 'Silver' => 'Silver', 'Gold' => 'Gold'])
                    ->default(null),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
