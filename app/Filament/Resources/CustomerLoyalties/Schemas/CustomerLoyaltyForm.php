<?php

namespace App\Filament\Resources\CustomerLoyalties\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerLoyaltyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->required(),
                TextInput::make('visit_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('tier_id')
                    ->relationship('tier', 'name')
                    ->required(),
                DateTimePicker::make('last_visit_at'),
            ]);
    }
}
